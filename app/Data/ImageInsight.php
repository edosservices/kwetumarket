<?php

namespace App\Data;

final class ImageInsight
{
    /**
     * @param  array<int, string>  $keywords
     * @param  array<int, string>  $attributes
     */
    public function __construct(
        public string $provider,
        public bool $limited,
        public ?string $label,
        public ?string $category,
        public ?string $subcategory,
        public ?string $brand,
        public ?string $model,
        public ?string $color,
        public ?string $shape,
        public ?string $visibleText,
        public array $keywords,
        public array $attributes,
        public float $confidence,
    ) {}

    /**
     * @return array<int, string>
     */
    public function signals(): array
    {
        $tokens = array_filter([
            $this->brand,
            $this->model,
            $this->category,
            $this->subcategory,
            $this->color,
            $this->shape,
            ...$this->keywords,
            ...$this->attributes,
            ...$this->textTokens(),
        ]);

        $normalized = [];

        foreach ($tokens as $token) {
            $token = mb_strtolower(trim((string) $token));

            if (mb_strlen($token) < 2) {
                continue;
            }

            $normalized[$token] = $token;
        }

        return array_values($normalized);
    }

    /**
     * @return array<int, string>
     */
    private function textTokens(): array
    {
        if ($this->visibleText === null || trim($this->visibleText) === '') {
            return [];
        }

        return preg_split('/\s+/u', mb_strtolower($this->visibleText)) ?: [];
    }
}
