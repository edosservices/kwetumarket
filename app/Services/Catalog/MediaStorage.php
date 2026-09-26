<?php

namespace App\Services\Catalog;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaStorage
{
    /**
     * @var array<string, string>
     */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public function store(UploadedFile $file, string $directory): string
    {
        $mime = (string) $file->getMimeType();
        $extension = self::MIME_EXTENSIONS[$mime] ?? null;
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $clientExtension = $clientExtension === 'jpeg' ? 'jpg' : $clientExtension;

        if ($extension === null || ! in_array($clientExtension, self::MIME_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'image' => __('ui.catalog.image_invalid'),
            ]);
        }

        $maxKilobytes = (int) config('twende.media.max_kilobytes');

        if ($file->getSize() > $maxKilobytes * 1024) {
            throw ValidationException::withMessages([
                'image' => __('ui.catalog.image_too_large'),
            ]);
        }

        $filename = (string) Str::uuid().'.'.$extension;

        return $file->storeAs(trim($directory, '/'), $filename, $this->disk());
    }

    public function delete(?string $path, ?string $disk = null): void
    {
        if ($path) {
            Storage::disk($disk ?? $this->disk())->delete($path);
        }
    }

    public function disk(): string
    {
        return (string) config('twende.media.disk');
    }
}
