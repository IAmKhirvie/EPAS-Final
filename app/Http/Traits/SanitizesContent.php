<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;

trait SanitizesContent
{
    /**
     * Sanitize HTML content to prevent XSS attacks while allowing basic formatting
     * Allowed tags: b, strong, i, em, u, br, p, ul, ol, li, code
     *
     * @param string|null $content
     * @return string|null
     */
    protected function sanitizeContent(?string $content): ?string
    {
        if (empty($content)) {
            return $content;
        }

        // Try HTML Purifier first for best security
        if (class_exists('HTMLPurifier')) {
            return $this->sanitizeWithHtmlPurifier($content);
        }

        // Fallback to basic sanitization
        return $this->basicSanitize($content);
    }

    /**
     * Sanitize content using HTML Purifier (most secure)
     */
    private function sanitizeWithHtmlPurifier(string $content): string
    {
        try {
            $config = \HTMLPurifier_Config::createDefault();

            // Only allow basic formatting tags
            $config->set('HTML.Allowed', 'b,strong,i,em,u,br,p,ul,ol,li,code');

            // No attributes allowed for maximum security
            $config->set('HTML.AllowedAttributes', '');

            // Disable auto-formatting to preserve user's intended formatting
            $config->set('AutoFormat.AutoParagraph', false);
            $config->set('AutoFormat.Linkify', false);
            $config->set('AutoFormat.RemoveEmpty', false);

            // Preserve newlines in the source
            $config->set('Core.NormalizeNewlines', false);
            $config->set('Core.CollectErrors', false);

            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($content);

        } catch (\Exception $e) {
            Log::error('HTMLPurifier error: ' . $e->getMessage());
            return $this->basicSanitize($content);
        }
    }

    /**
     * Basic fallback sanitization if HTML Purifier is not available
     */
    private function basicSanitize(string $content): string
    {
        Log::warning('HTMLPurifier not available, using fallback sanitizer. Install ezyang/htmlpurifier for better XSS protection.');

        // Safe fallback: strip all tags except basic formatting (no attributes allowed)
        $allowed = '<b><i><u><strong><em><p><br><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code>';
        $content = strip_tags($content, $allowed);

        // Remove any attributes from the remaining allowed tags to prevent XSS
        $content = preg_replace('/<(\w+)\s+[^>]*>/', '<$1>', $content);

        return $content;
    }

    /**
     * Sanitize an array of content fields
     *
     * @param array $data
     * @param array $fields Fields to sanitize
     * @return array
     */
    protected function sanitizeFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = $this->sanitizeContent($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Strip all HTML tags (for plain text fields)
     */
    protected function stripHtml(?string $content): ?string
    {
        if (empty($content)) {
            return $content;
        }

        return strip_tags($content);
    }
}
