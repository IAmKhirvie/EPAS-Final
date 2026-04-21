<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMedia
{
    /**
     * Get all media for this model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->ordered();
    }

    /**
     * Add media from an uploaded file.
     */
    public function addMedia(UploadedFile $file, string $collection = 'default'): Media
    {
        $fileName = $this->generateFileName($file);
        $path = $collection . '/' . $fileName;

        Storage::disk('public')->put($path, file_get_contents($file));

        return $this->media()->create([
            'collection_name' => $collection,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'size' => $file->getSize(),
            'order_column' => $this->media()->count() + 1,
        ]);
    }

    /**
     * Add media from a URL.
     */
    public function addMediaFromUrl(string $url, string $collection = 'default'): Media
    {
        $contents = @file_get_contents($url);
        if ($contents === false) {
            throw new \RuntimeException("Failed to fetch media from URL: {$url}");
        }
        $fileName = Str::uuid() . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $path = $collection . '/' . $fileName;

        Storage::disk('public')->put($path, $contents);

        $mimeType = Storage::disk('public')->mimeType($path);
        $size = Storage::disk('public')->size($path);

        return $this->media()->create([
            'collection_name' => $collection,
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => 'public',
            'size' => $size,
            'order_column' => $this->media()->count() + 1,
        ]);
    }

    /**
     * Add media from a base64 string.
     */
    public function addMediaFromBase64(string $base64, string $collection = 'default', string $extension = 'png'): Media
    {
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        $maxSize = config('joms.uploads.max_image_size', 5120) * 1024; // convert KB to bytes
        if (strlen($data) > $maxSize) {
            throw new \RuntimeException('Base64 media exceeds maximum allowed size.');
        }
        $fileName = Str::uuid() . '.' . $extension;
        $path = $collection . '/' . $fileName;

        Storage::disk('public')->put($path, $data);

        $mimeType = Storage::disk('public')->mimeType($path);
        $size = Storage::disk('public')->size($path);

        return $this->media()->create([
            'collection_name' => $collection,
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => 'public',
            'size' => $size,
            'order_column' => $this->media()->count() + 1,
        ]);
    }

    /**
     * Get media from a specific collection.
     */
    public function getMedia(string $collection = 'default')
    {
        return $this->media()->where('collection_name', $collection)->get();
    }

    /**
     * Get the first media from a collection.
     */
    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->media()->where('collection_name', $collection)->first();
    }

    /**
     * Get the first media URL from a collection.
     */
    public function getFirstMediaUrl(string $collection = 'default'): ?string
    {
        $media = $this->getFirstMedia($collection);
        return $media ? $media->url : null;
    }

    /**
     * Check if the model has media in a collection.
     */
    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->media()->where('collection_name', $collection)->exists();
    }

    /**
     * Clear all media from a collection.
     */
    public function clearMediaCollection(string $collection = 'default'): void
    {
        $this->media()->where('collection_name', $collection)->each(function ($media) {
            $media->delete();
        });
    }

    /**
     * Delete all media when the model is deleted.
     */
    protected static function bootHasMedia(): void
    {
        static::deleting(function ($model) {
            $model->media->each(function ($media) {
                $media->delete();
            });
        });
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
     * Update media order.
     */
    public function updateMediaOrder(array $order): void
    {
        foreach ($order as $index => $mediaId) {
            $this->media()->where('id', $mediaId)->update(['order_column' => $index + 1]);
        }
    }
}
