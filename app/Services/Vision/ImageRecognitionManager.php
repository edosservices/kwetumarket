<?php

namespace App\Services\Vision;

use App\Contracts\ImageRecognitionInterface;
use App\Data\ImageInsight;
use Throwable;

class ImageRecognitionManager implements ImageRecognitionInterface
{
    public function __construct(
        private LocalImageAnalyzer $local,
        private OpenAiVisionRecognizer $openAi,
        private GoogleVisionRecognizer $google,
        private AwsRekognitionRecognizer $aws,
    ) {}

    public function name(): string
    {
        $configured = $this->configured();

        return $configured->available() ? $configured->name() : $this->local->name();
    }

    public function available(): bool
    {
        return $this->configured()->available();
    }

    public function analyze(string $absolutePath): ImageInsight
    {
        $configured = $this->configured();

        if ($configured->name() !== $this->local->name() && $configured->available()) {
            try {
                return $configured->analyze($absolutePath);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $this->local->analyze($absolutePath);
    }

    private function configured(): ImageRecognitionInterface
    {
        return match (config('twende.vision.driver')) {
            'openai' => $this->openAi,
            'google' => $this->google,
            'aws' => $this->aws,
            default => $this->local,
        };
    }
}
