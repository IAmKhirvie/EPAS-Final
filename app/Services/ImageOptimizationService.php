<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    /**
     * Optimize an uploaded image file: resize and compress.
     * Returns the stored file path.
     */
    public function optimizeUpload(UploadedFile $file, string $directory, int $maxWidth = 1200, int $quality = 80): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // If Intervention Image is available, resize
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $image = $manager->read($file->getRealPath());

            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $encoded = $image->toJpeg($quality);
            $filename = Str::uuid() . '.jpg';
            Storage::disk('public')->put("{$directory}/{$filename}", (string) $encoded);
        } else {
            // Fallback: store as-is
            $file->storeAs($directory, $filename, 'public');
        }

        return "{$directory}/{$filename}";
    }

    /**
     * Optimize a base64-encoded image (e.g., cropped avatar).
     * Returns the stored file path.
     */
    public function optimizeBase64(string $base64Data, string $directory, int $maxWidth = 400, int $quality = 80): string
    {
        // Extract the image data from base64
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
        $imageData = base64_decode($imageData);

        $filename = Str::uuid() . '.jpg';

        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $image = $manager->read($imageData);

            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $encoded = $image->toJpeg($quality);
            Storage::disk('public')->put("{$directory}/{$filename}", (string) $encoded);
        } else {
            // Fallback: store as-is
            Storage::disk('public')->put("{$directory}/{$filename}", $imageData);
        }

        return "{$directory}/{$filename}";
    }
}
