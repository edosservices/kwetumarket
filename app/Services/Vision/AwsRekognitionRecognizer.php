<?php

namespace App\Services\Vision;

use App\Contracts\ImageRecognitionInterface;
use App\Data\ImageInsight;
use RuntimeException;

class AwsRekognitionRecognizer implements ImageRecognitionInterface
{
    public function name(): string
    {
        return 'aws';
    }

    public function available(): bool
    {
        return false;
    }

    public function analyze(string $absolutePath): ImageInsight
    {
        throw new RuntimeException('AWS Rekognition is not connected yet.');
    }
}
