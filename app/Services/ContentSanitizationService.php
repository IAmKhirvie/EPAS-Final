<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ContentSanitizationService
 *
 * Handles HTML content sanitization and part/image processing for topics.
 * Uses HTML Purifier when available, with a basic fallback sanitizer.
 */
class ContentSanitizationService
{
    /**
     * Remove MS Word HTML bloat (XML, conditional comments, MSO styles).
     * Preserves table attributes and legitimate HTML formatting.
     */
    public function stripWordBloat($content): string
    {
        if (!$content) return '';

        // Remove all MSO conditional comments: <!--[if gte mso 9]>...<![endif]-->
        $content = preg_replace('/<!--\[if\s+gte\s+mso\s+\d+\]>.*?<!\[endif\]-->/is', '', $content);

        // Remove all XML blocks: <xml>...</xml>
        $content = preg_replace('/<xml[^>]*>.*?<\/xml>/is', '', $content);

        // Remove <o:p> tags (MS Word paragraph markers)
        $content = preg_replace('/<\/?o:p[^>]*>/i', '', $content);

        // Remove StartFragment/EndFragment comments
        $content = str_replace(['<!--StartFragment-->', '<!--EndFragment-->'], '', $content);

        // Remove page-break <br> tags
        $content = preg_replace('/<br[^>]*clear="all"[^>]*>/i', '', $content);

        // Clean MSO-specific style properties but preserve other styles
        $content = preg_replace_callback('/style="([^"]*)"/i', function($matches) {
            $style = $matches[1];
            // Remove MSO-specific properties
            $style = preg_replace('/\s*mso-[^;:]+:[^;]+;?/i', '', $style);
            // Remove font references to MS fonts
            $style = preg_replace('/\s*font-family:\s*(ＭＳ\s*明朝|MS\s*Gothic|Times\s*New\s*Roman)[^;]*;?/i', '', $style);
            // Remove font-size in pt
            $style = preg_replace('/\s*font-size:\s*\d+\.?\d*pt;?/i', '', $style);
            $style = trim($style);
            return $style ? "style=\"{$style}\"" : '';
        }, $content);

        // Remove empty style attributes
        $content = preg_replace('/\s*style="\s*"/', '', $content);

        // Remove MS Word class names only (preserve other classes)
        $content = preg_replace('/\s*class="Mso[A-Za-z0-9\s]*"/i', '', $content);
        $content = preg_replace('/\s*class=""/', '', $content);

        // Remove empty paragraphs (but preserve those with &nbsp; or content)
        $content = preg_replace('/<p[^>]*>\s*<o:p>\s*<\/o:p>\s*<\/p>/i', '', $content);
        $content = preg_replace('/<p[^>]*>\s*&nbsp;\s*<\/p>/i', '', $content);

        // Clean up multiple consecutive line breaks
        $content = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $content);

        return trim($content);
    }

    /**
     * Most Secure: Using HTML Purifier
     * Protects against all known XSS attacks while allowing basic formatting
     */
    public function sanitizeWithHtmlPurifier($content)
    {
        // Check if HTML Purifier is available
        if (!class_exists('HTMLPurifier')) {
            Log::warning('HTMLPurifier not found, using fallback sanitization');
            // Fallback to basic security if HTML Purifier isn't installed
            return $this->basicFallbackSanitize($content);
        }

        try {
            $config = \HTMLPurifier_Config::createDefault();

            // Allow formatting tags, tables, images, and headings for rich content
            $config->set('HTML.Allowed', 'b,strong,i,em,u,br,p,ul,ol,li,code,img[src|alt|width|height],table[border|cellpadding|cellspacing],thead,tbody,tr,th,td,div,h1,h2,h3,h4,h5,h6,span,a[href|target],sup,sub');
            $config->set('CSS.AllowedProperties', 'text-align,font-weight,font-style,text-decoration,width,height,border,border-collapse,padding,margin,vertical-align,background-color,color');
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'data' => true]);

            // Disable auto-formatting to preserve user's intended formatting
            $config->set('AutoFormat.AutoParagraph', false);
            $config->set('AutoFormat.Linkify', false);
            $config->set('AutoFormat.RemoveEmpty', false);

            // Preserve newlines in the source
            $config->set('Core.NormalizeNewlines', false);
            $config->set('Core.CollectErrors', false);

            $purifier = new \HTMLPurifier($config);
            $cleaned = $purifier->purify($content);

            // DON'T convert newlines to <br> tags here - store raw content
            // The conversion will happen only when displaying to users
            return $cleaned;

        } catch (\Exception $e) {
            Log::error('HTMLPurifier error: ' . $e->getMessage());
            return $this->basicFallbackSanitize($content);
        }
    }

    /**
     * Basic fallback sanitization if HTML Purifier is not available
     * Still provides good security but not as comprehensive as HTML Purifier
     */
    public function basicFallbackSanitize($content)
    {
        // Remove NULL bytes
        $content = str_replace("\0", '', $content);

        // Convert all special characters to HTML entities
        $content = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Define allowed tags and their safe replacements
        $allowedTags = [
            'b' => '&lt;b&gt;',
            'strong' => '&lt;strong&gt;',
            'i' => '&lt;i&gt;',
            'em' => '&lt;em&gt;',
            'u' => '&lt;u&gt;',
            'br' => '&lt;br&gt;',
            'p' => '&lt;p&gt;',
            'ul' => '&lt;ul&gt;',
            'ol' => '&lt;ol&gt;',
            'li' => '&lt;li&gt;',
            'code' => '&lt;code&gt;'
        ];

        $closingTags = [
            'b' => '&lt;/b&gt;',
            'strong' => '&lt;/strong&gt;',
            'i' => '&lt;/i&gt;',
            'em' => '&lt;/em&gt;',
            'u' => '&lt;/u&gt;',
            'p' => '&lt;/p&gt;',
            'ul' => '&lt;/ul&gt;',
            'ol' => '&lt;/ol&gt;',
            'li' => '&lt;/li&gt;',
            'code' => '&lt;/code&gt;'
        ];

        // Restore allowed opening tags
        foreach ($allowedTags as $tag => $entity) {
            $content = str_replace($entity, "<$tag>", $content);
        }

        // Restore allowed closing tags
        foreach ($closingTags as $tag => $entity) {
            $content = str_replace($entity, "</$tag>", $content);
        }

        // DON'T convert newlines to <br> tags here either
        return $content;
    }

    /**
     * Sanitize HTML content within blocks.
     */
    public function sanitizeBlocks(array $blocks): array
    {
        foreach ($blocks as &$block) {
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];

            // Sanitize HTML content in blocks that contain rich text
            if (in_array($type, ['text', 'image_text', 'table', 'callout']) && !empty($data['content'])) {
                $data['content'] = $this->sanitizeWithHtmlPurifier($data['content']);
            }

            // Sanitize document_content in document blocks
            if ($type === 'document' && !empty($data['document_content'])) {
                $data['document_content'] = $this->sanitizeWithHtmlPurifier($data['document_content']);
            }

            $block['data'] = $data;
        }

        return $blocks;
    }

    /**
     * Process image uploads for blocks (image and image_text types).
     */
    public function processBlockImages(Request $request, array $blocks, ?array $existingBlocks = null): array
    {
        $existingMap = [];
        if ($existingBlocks) {
            foreach ($existingBlocks as $eb) {
                if (!empty($eb['id'])) {
                    $existingMap[$eb['id']] = $eb;
                }
            }
        }

        foreach ($blocks as &$block) {
            $type = $block['type'] ?? '';
            $blockId = $block['id'] ?? '';

            if (!in_array($type, ['image', 'image_text'])) {
                continue;
            }

            // Check if a new image was uploaded for this block
            if ($request->hasFile("block_images.{$blockId}")) {
                $image = $request->file("block_images.{$blockId}");
                $imageName = 'block_' . time() . '_' . Str::random(8) . '.' . $image->extension();
                $image->storeAs('topic-images', $imageName, 'public');
                $block['data']['image'] = asset('storage/topic-images/' . $imageName);

                // Delete old image if this block had one
                if (isset($existingMap[$blockId]['data']['image'])) {
                    $oldImage = $existingMap[$blockId]['data']['image'];
                    $oldFilename = basename($oldImage);
                    Storage::disk('public')->delete('topic-images/' . $oldFilename);
                }
            } elseif (!empty($block['data']['image'])) {
                // Keep existing image reference
            } elseif (isset($existingMap[$blockId]['data']['image'])) {
                // Preserve existing image if not explicitly cleared
                $block['data']['image'] = $existingMap[$blockId]['data']['image'];
            }
        }

        return $blocks;
    }

    /**
     * Process document uploads for document blocks.
     */
    public function processBlockDocuments(Request $request, array $blocks, ?array $existingBlocks = null): array
    {
        $existingMap = [];
        if ($existingBlocks) {
            foreach ($existingBlocks as $eb) {
                if (!empty($eb['id'])) {
                    $existingMap[$eb['id']] = $eb;
                }
            }
        }

        foreach ($blocks as &$block) {
            if (($block['type'] ?? '') !== 'document') {
                continue;
            }

            $blockId = $block['id'] ?? '';

            if ($request->hasFile("block_documents.{$blockId}")) {
                $file = $request->file("block_documents.{$blockId}");
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                $block['data']['file_path'] = $file->storeAs('topics', $filename, 'public');
                $block['data']['original_filename'] = $file->getClientOriginalName();

                // Convert document to HTML
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                    $block['data']['document_content'] = app(DocumentConversionService::class)
                        ->convertToHtml(Storage::disk('public')->path($block['data']['file_path']), $ext);
                }

                // Delete old file if this block had one
                if (isset($existingMap[$blockId]['data']['file_path'])) {
                    Storage::disk('public')->delete($existingMap[$blockId]['data']['file_path']);
                }
            } elseif (!empty($block['data']['file_path'])) {
                // Keep existing file reference — also preserve document_content from existing
                if (isset($existingMap[$blockId]['data']['document_content'])) {
                    $block['data']['document_content'] = $existingMap[$blockId]['data']['document_content'];
                }
            } elseif (isset($existingMap[$blockId])) {
                // Preserve existing document data
                $block['data']['file_path'] = $existingMap[$blockId]['data']['file_path'] ?? null;
                $block['data']['original_filename'] = $existingMap[$blockId]['data']['original_filename'] ?? null;
                $block['data']['document_content'] = $existingMap[$blockId]['data']['document_content'] ?? null;
            }
        }

        return $blocks;
    }

    /**
     * Process parts with image uploads
     */
    public function processPartsWithImages(Request $request, array $parts, array $existingParts = []): array
    {
        $processedParts = [];

        foreach ($parts as $index => $part) {
            $processedPart = [
                'title' => $part['title'] ?? '',
                'explanation' => $part['explanation'] ?? '',
                'image' => null,
            ];

            // Check if a new image was uploaded for this part
            if ($request->hasFile("part_images.{$index}")) {
                $image = $request->file("part_images.{$index}");
                $imageName = 'topic_part_' . time() . '_' . $index . '.' . $image->extension();
                $image->storeAs('topic-images', $imageName, 'public');
                $processedPart['image'] = asset('storage/topic-images/' . $imageName);

                // Delete old image if exists
                if (!empty($part['existing_image'])) {
                    $oldFilename = basename($part['existing_image']);
                    Storage::disk('public')->delete('topic-images/' . $oldFilename);
                }
            } elseif (!empty($part['existing_image'])) {
                // Keep existing image if no new one uploaded
                $processedPart['image'] = $part['existing_image'];
            }

            // Only add part if it has meaningful content
            if (!empty($processedPart['title']) || !empty($processedPart['explanation']) || !empty($processedPart['image'])) {
                $processedParts[] = $processedPart;
            }
        }

        return $processedParts;
    }
}
