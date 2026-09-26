<?php

namespace App\Services\Search;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SearchImageStorage
{
    /**
     * @var array<string, string>
     */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function store(UploadedFile $file): string
    {
        $path = $file->getPathname();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: '';
        $extension = self::MIME_EXTENSIONS[$mime] ?? null;
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $clientExtension = $clientExtension === 'jpeg' ? 'jpg' : $clientExtension;
        $allowedClients = $extension === 'jpg' ? ['jpg', 'jpeg'] : [$extension];

        if ($extension === null || ! in_array($clientExtension, $allowedClients, true)) {
            throw ValidationException::withMessages([
                'image' => __('ui.smart.image_invalid'),
            ]);
        }

        $maxBytes = (int) config('twende.vision.max_kilobytes') * 1024;

        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'image' => __('ui.smart.image_too_large'),
            ]);
        }

        $info = @getimagesize($path);

        if ($info === false) {
            throw ValidationException::withMessages([
                'image' => __('ui.smart.image_invalid'),
            ]);
        }

        $min = (int) config('twende.vision.min_edge');
        $max = (int) config('twende.vision.max_edge');
        $width = (int) $info[0];
        $height = (int) $info[1];

        if ($width < $min || $height < $min || $width > $max || $height > $max) {
            throw ValidationException::withMessages([
                'image' => __('ui.smart.image_dimensions'),
            ]);
        }

        if (! $this->readable($mime, $path)) {
            throw ValidationException::withMessages([
                'image' => __('ui.smart.image_invalid'),
            ]);
        }

        $filename = (string) Str::uuid().'.'.$extension;

        return $file->storeAs(
            trim((string) config('twende.vision.directory'), '/'),
            $filename,
            $this->disk(),
        );
    }

    public function disk(): string
    {
        return (string) config('twende.media.disk');
    }

    public function delete(string $path, ?string $disk = null): void
    {
        Storage::disk($disk ?? $this->disk())->delete($path);
    }

    private function readable(string $mime, string $path): bool
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image instanceof \GdImage) {
            return false;
        }

        imagedestroy($image);

        return true;
    }
}
