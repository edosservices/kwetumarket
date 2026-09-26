<?php

namespace App\Services\Vision;

use App\Contracts\ImageRecognitionInterface;
use App\Data\ImageInsight;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiVisionRecognizer implements ImageRecognitionInterface
{
    public function name(): string
    {
        return 'openai';
    }

    public function available(): bool
    {
        return filled(config('twende.vision.openai.key'));
    }

    public function analyze(string $absolutePath): ImageInsight
    {
        if (! $this->available()) {
            throw new RuntimeException('OpenAI Vision is not configured.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($absolutePath) ?: 'image/jpeg';
        $encoded = base64_encode((string) file_get_contents($absolutePath));

        $response = Http::withToken((string) config('twende.vision.openai.key'))
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('twende.vision.openai.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Identify the product in this photo. Reply with JSON keys label, category, subcategory, brand, model, color, shape, visible_text, keywords, traits, confidence. Use null when unsure. confidence is 0 to 1. Do not invent a brand.',
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => 'data:'.$mime.';base64,'.$encoded],
                        ],
                    ],
                ]],
            ])
            ->throw()
            ->json();

        $content = $response['choices'][0]['message']['content'] ?? null;
        $parsed = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($parsed)) {
            throw new RuntimeException('OpenAI Vision returned an unreadable response.');
        }

        return new ImageInsight(
            provider: $this->name(),
            limited: false,
            label: $this->stringOrNull($parsed['label'] ?? null),
            category: $this->stringOrNull($parsed['category'] ?? null),
            subcategory: $this->stringOrNull($parsed['subcategory'] ?? null),
            brand: $this->stringOrNull($parsed['brand'] ?? null),
            model: $this->stringOrNull($parsed['model'] ?? null),
            color: $this->stringOrNull($parsed['color'] ?? null),
            shape: $this->stringOrNull($parsed['shape'] ?? null),
            visibleText: $this->stringOrNull($parsed['visible_text'] ?? null),
            keywords: $this->strings($parsed['keywords'] ?? []),
            attributes: $this->strings($parsed['traits'] ?? []),
            confidence: max(0, min(1, (float) ($parsed['confidence'] ?? 0))),
        );
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, string>
     */
    private function strings(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value) => is_string($value) ? trim($value) : '',
            $values,
        )));
    }
}
