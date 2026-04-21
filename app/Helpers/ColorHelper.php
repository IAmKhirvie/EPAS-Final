<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Darken a hex color by a percentage
     *
     * @param string $hex Hex color (e.g., "#3b82f6" or "3b82f6")
     * @param int $percent Percentage to darken (0-100)
     * @return string Darkened hex color
     */
    public static function darken(string $hex, int $percent): string
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Darken
        $r = max(0, $r - ($r * $percent / 100));
        $g = max(0, $g - ($g * $percent / 100));
        $b = max(0, $b - ($b * $percent / 100));

        // Convert back to hex
        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    /**
     * Lighten a hex color by a percentage
     *
     * @param string $hex Hex color (e.g., "#3b82f6" or "3b82f6")
     * @param int $percent Percentage to lighten (0-100)
     * @return string Lightened hex color
     */
    public static function lighten(string $hex, int $percent): string
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Lighten
        $r = min(255, $r + ((255 - $r) * $percent / 100));
        $g = min(255, $g + ((255 - $g) * $percent / 100));
        $b = min(255, $b + ((255 - $b) * $percent / 100));

        // Convert back to hex
        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    /**
     * Check if a color is considered "light" (for determining text contrast)
     *
     * @param string $hex Hex color
     * @return bool True if the color is light
     */
    public static function isLight(string $hex): bool
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Calculate perceived brightness
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $brightness > 128;
    }
}
