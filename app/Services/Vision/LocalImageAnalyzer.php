<?php

namespace App\Services\Vision;

use App\Contracts\ImageRecognitionInterface;
use App\Data\ImageInsight;

class LocalImageAnalyzer implements ImageRecognitionInterface
{
    public function name(): string
    {
        return 'local';
    }

    public function available(): bool
    {
        return function_exists('imagecreatetruecolor');
    }

    public function analyze(string $absolutePath): ImageInsight
    {
        $color = $this->dominantColor($absolutePath);
        $keywords = $color === null ? [] : $this->colorKeywords($color);

        return new ImageInsight(
            provider: $this->name(),
            limited: true,
            label: null,
            category: null,
            subcategory: null,
            brand: null,
            model: null,
            color: $color,
            shape: null,
            visibleText: null,
            keywords: $keywords,
            attributes: $color === null ? [] : [$color],
            confidence: $color === null ? 0.1 : 0.35,
        );
    }

    private function dominantColor(string $absolutePath): ?string
    {
        $image = $this->open($absolutePath);

        if ($image === null) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $sample = imagecreatetruecolor(24, 24);
        imagecopyresampled($sample, $image, 0, 0, 0, 0, 24, 24, $width, $height);
        imagedestroy($image);

        $counts = [];

        for ($y = 0; $y < 24; $y++) {
            for ($x = 0; $x < 24; $x++) {
                $rgb = imagecolorat($sample, $x, $y);
                $name = $this->colorName(($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF);
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }

        imagedestroy($sample);
        arsort($counts);
        $winner = (string) array_key_first($counts);

        if (($counts[$winner] ?? 0) < 80 || $winner === 'multicolore') {
            return null;
        }

        return $winner;
    }

    private function open(string $absolutePath): ?\GdImage
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($absolutePath);
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };

        return $image instanceof \GdImage ? $image : null;
    }

    private function colorName(int $red, int $green, int $blue): string
    {
        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);

        if ($max - $min < 18) {
            if ($max < 50) {
                return 'noir';
            }

            if ($max > 210) {
                return 'blanc';
            }

            return 'gris';
        }

        if ($blue >= $red && $blue >= $green && $blue - $red > 20) {
            return 'bleu';
        }

        if ($green >= $red && $green >= $blue && $green - $red > 20) {
            return 'vert';
        }

        if ($red > 180 && $green > 140 && $blue < 90) {
            return 'jaune';
        }

        if ($red >= $green && $red >= $blue && $red - $blue > 30 && $green < 140) {
            return 'rouge';
        }

        if ($red > 160 && $blue > 140 && $green < 130) {
            return 'violet';
        }

        return 'multicolore';
    }

    /**
     * @return array<int, string>
     */
    private function colorKeywords(string $color): array
    {
        $english = [
            'noir' => 'black',
            'blanc' => 'white',
            'gris' => 'gray',
            'bleu' => 'blue',
            'vert' => 'green',
            'jaune' => 'yellow',
            'rouge' => 'red',
            'violet' => 'purple',
        ];

        return array_values(array_unique(array_filter([$color, $english[$color] ?? null])));
    }
}
