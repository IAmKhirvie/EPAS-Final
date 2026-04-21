<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaService
{
    protected string $disk = 'public';

    /**
     * Upload a file and create a media record.
     */
    public function upload(UploadedFile $file, string $collection = 'default', array $customProperties = []): Media
    {
        $fileName = $this->generateFileName($file);
        $path = $collection . '/' . $fileName;

        Storage::disk($this->disk)->put($path, file_get_contents($file));

        return Media::create([
            'collection_name' => $collection,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'disk' => $this->disk,
            'size' => $file->getSize(),
            'custom_properties' => $customProperties,
        ]);
    }

    /**
     * Upload an image with optional resizing.
     */
    public function uploadImage(
        UploadedFile $file,
        string $collection = 'images',
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        int $quality = 85
    ): Media {
        $fileName = $this->generateFileName($file);
        $path = $collection . '/' . $fileName;

        if ($maxWidth || $maxHeight) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            if ($maxWidth && $maxHeight) {
                $image->scaleDown($maxWidth, $maxHeight);
            } elseif ($maxWidth) {
                $image->scaleDown(width: $maxWidth);
            } else {
                $image->scaleDown(height: $maxHeight);
            }

            $encoded = $image->toJpeg($quality);
            Storage::disk($this->disk)->put($path, $encoded);
            $size = Storage::disk($this->disk)->size($path);
        } else {
            Storage::disk($this->disk)->put($path, file_get_contents($file));
            $size = $file->getSize();
        }

        return Media::create([
            'collection_name' => $collection,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'disk' => $this->disk,
            'size' => $size,
        ]);
    }

    /**
     * Delete a media file and its record.
     */
    public function delete(Media $media): bool
    {
        // Delete responsive images if they exist
        if ($media->responsive_images) {
            foreach ($media->responsive_images as $responsive) {
                $path = $media->collection_name . '/responsive/' . $responsive['file_name'];
                Storage::disk($media->disk)->delete($path);
            }
        }

        // Delete the original file
        Storage::disk($media->disk)->delete($media->getPath());

        return $media->delete();
    }

    /**
     * Move media to a different collection.
     */
    public function moveToCollection(Media $media, string $newCollection): Media
    {
        $oldPath = $media->getPath();
        $newPath = $newCollection . '/' . $media->file_name;

        Storage::disk($media->disk)->move($oldPath, $newPath);

        $media->update(['collection_name' => $newCollection]);

        return $media;
    }

    /**
     * Copy media to a new record.
     */
    public function copy(Media $media, ?string $newCollection = null): Media
    {
        $collection = $newCollection ?? $media->collection_name;
        $newFileName = $this->generateUniqueFileName($media->file_name);
        $newPath = $collection . '/' . $newFileName;

        Storage::disk($media->disk)->copy($media->getPath(), $newPath);

        return Media::create([
            'collection_name' => $collection,
            'name' => $media->name,
            'file_name' => $newFileName,
            'mime_type' => $media->mime_type,
            'disk' => $media->disk,
            'size' => $media->size,
            'custom_properties' => $media->custom_properties,
        ]);
    }

    /**
     * Get all media in a collection.
     */
    public function getByCollection(string $collection)
    {
        return Media::inCollection($collection)->ordered()->get();
    }

    /**
     * Get total storage used by a collection.
     */
    public function getCollectionSize(string $collection): int
    {
        return Media::inCollection($collection)->sum('size');
    }

    /**
     * Generate a unique file name.
     */
    protected function generateFileName(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Generate a unique file name from an existing file name.
     */
    protected function generateUniqueFileName(string $fileName): string
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        return Str::uuid() . '.' . $extension;
    }

}
