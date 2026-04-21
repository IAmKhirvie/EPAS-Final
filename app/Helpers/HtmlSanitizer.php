<?php

namespace App\Helpers;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags for rich text content
     */
    protected static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'ul', 'ol', 'li', 'a', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td'
    ];

    /**
     * Allowed attributes per tag
     */
    protected static array $allowedAttributes = [
        'a' => ['href', 'target', 'rel'],
        'span' => ['style'],
        'div' => ['style'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /**
     * Allowed CSS properties for style attributes
     */
    protected static array $allowedStyles = [
        'text-decoration', 'text-align', 'font-weight', 'font-style',
        'color', 'background-color'
    ];

    /**
     * Sanitize HTML content to prevent XSS attacks
     */
    public static function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Remove script tags and their contents
        $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $html);

        // Remove style tags and their contents
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/i', '', $html);

        // Remove event handlers (onclick, onerror, etc.)
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $html);

        // Remove javascript: URLs
        $html = preg_replace('/javascript\s*:/i', '', $html);
        $html = preg_replace('/vbscript\s*:/i', '', $html);
        $html = preg_replace('/data\s*:/i', 'data-blocked:', $html);

        // Remove any remaining dangerous protocols
        $html = preg_replace('/(href|src)\s*=\s*["\']?\s*(javascript|vbscript|data):/i', '$1=""', $html);

        // Use DOMDocument for proper HTML parsing
        if (class_exists('DOMDocument')) {
            $html = self::sanitizeWithDom($html);
        } else {
            // Fallback: strip all tags except allowed ones
            $html = strip_tags($html, '<' . implode('><', self::$allowedTags) . '>');
        }

        return trim($html);
    }

    /**
     * Sanitize HTML using DOMDocument for proper parsing
     */
    protected static function sanitizeWithDom(string $html): string
    {
        // Suppress errors for malformed HTML
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Wrap in a container to handle fragments
        $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        // Process all elements
        self::processNode($dom->documentElement);

        // Extract body content
        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return strip_tags($html, '<' . implode('><', self::$allowedTags) . '>');
        }

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Recursively process DOM nodes
     */
    protected static function processNode(\DOMNode $node): void
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        /** @var \DOMElement $node */
        $tagName = strtolower($node->tagName);

        // Remove disallowed tags but keep their content
        if (!in_array($tagName, self::$allowedTags) && !in_array($tagName, ['html', 'head', 'body', 'meta'])) {
            $fragment = $node->ownerDocument->createDocumentFragment();
            while ($node->firstChild) {
                $fragment->appendChild($node->firstChild);
            }
            if ($node->parentNode) {
                $node->parentNode->replaceChild($fragment, $node);
            }
            return;
        }

        // Remove disallowed attributes
        $allowedAttrs = self::$allowedAttributes[$tagName] ?? [];
        $attrsToRemove = [];

        foreach ($node->attributes as $attr) {
            $attrName = strtolower($attr->name);

            if (!in_array($attrName, $allowedAttrs)) {
                $attrsToRemove[] = $attr->name;
                continue;
            }

            // Sanitize href attributes
            if ($attrName === 'href') {
                $value = strtolower(trim($attr->value));
                if (preg_match('/^(javascript|vbscript|data):/i', $value)) {
                    $attrsToRemove[] = $attr->name;
                    continue;
                }
            }

            // Sanitize style attributes
            if ($attrName === 'style') {
                $attr->value = self::sanitizeStyle($attr->value);
            }
        }

        foreach ($attrsToRemove as $attrName) {
            $node->removeAttribute($attrName);
        }

        // Add rel="noopener noreferrer" to external links
        if ($tagName === 'a' && $node->hasAttribute('href')) {
            $href = $node->getAttribute('href');
            if (preg_match('/^https?:\/\//i', $href)) {
                $node->setAttribute('rel', 'noopener noreferrer');
                $node->setAttribute('target', '_blank');
            }
        }

        // Process child nodes
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            self::processNode($child);
        }
    }

    /**
     * Sanitize CSS style attribute
     */
    protected static function sanitizeStyle(string $style): string
    {
        $sanitized = [];
        $declarations = explode(';', $style);

        foreach ($declarations as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) continue;

            $property = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            if (in_array($property, self::$allowedStyles)) {
                // Remove any url() or expression() from values
                if (!preg_match('/(url|expression|javascript)/i', $value)) {
                    $sanitized[] = $property . ': ' . $value;
                }
            }
        }

        return implode('; ', $sanitized);
    }

    /**
     * Convert plain text to safe HTML (escape special chars, convert newlines)
     */
    public static function plainToHtml(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        return nl2br(e($text));
    }

    /**
     * Strip all HTML tags and return plain text
     */
    public static function toPlainText(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Remove HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
