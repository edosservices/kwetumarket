<?php

namespace App\Services\Vision;

use App\Contracts\ImageRecognitionInterface;
use App\Data\ImageInsight;
use RuntimeException;

class GoogleVisionRecognizer implements ImageRecognitionInterface
{
    public function name(): string
    {
        return 'google';
    }

    public function available(): bool
    {
        return false;
    }

    public function analyze(string $absolutePath): ImageInsight
    {
        throw new RuntimeException('Google Vision is not connected yet.');
    }
}
