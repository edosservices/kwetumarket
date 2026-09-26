<?php

namespace App\Contracts;

use App\Data\ImageInsight;

interface ImageRecognitionInterface
{
    public function name(): string;

    public function available(): bool;

    public function analyze(string $absolutePath): ImageInsight;
}
