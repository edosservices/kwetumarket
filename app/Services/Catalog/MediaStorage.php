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
        'application/pdf' => 'pdf',
    ];

    public function store(UploadedFile $file, string $directory, ?string $disk = null): string
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

        $info = @getimagesize($file->getPathname());

        if ($info === false || $info[0] < 1 || $info[1] < 1 || $info[0] > 8000 || $info[1] > 8000) {
            throw ValidationException::withMessages([
                'image' => __('ui.catalog.image_invalid'),
            ]);
        }

        $filename = (string) Str::uuid().'.'.$extension;

        return $file->storeAs(trim($directory, '/'), $filename, $disk ?? $this->disk());
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

    public function thumbnail(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->disk();

        if (! function_exists('imagecreatefromstring') || ! in_array(config("filesystems.disks.{$disk}.driver"), ['local'], true)) {
            return null;
        }

        $absolute = Storage::disk($disk)->path($path);

        if (! is_file($absolute)) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($absolute));

        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, 480 / max($width, $height, 1));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $thumb = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        $relative = trim(dirname($path), '.').'/thumbs/'.pathinfo($path, PATHINFO_FILENAME).'.jpg';
        $destination = Storage::disk($disk)->path($relative);
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagejpeg($thumb, $destination, 82);
        imagedestroy($source);
        imagedestroy($thumb);

        return $relative;
    }
}
