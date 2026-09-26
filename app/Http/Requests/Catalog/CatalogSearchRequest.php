<?php

namespace App\Http\Requests\Catalog;

use App\Enums\ProductCondition;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class CatalogSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'brand' => ['nullable', 'integer', 'exists:brands,id'],
            'shop' => ['nullable', 'integer', 'exists:shops,id'],
            'price_min' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'price_max' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'availability' => ['nullable', Rule::in(['in_stock', 'out_of_stock'])],
            'condition' => ['nullable', Rule::in(ProductCondition::values())],
            'sort' => ['nullable', Rule::in(['relevance', 'price_asc', 'price_desc', 'newest'])],
        ];
    }

    public function term(): string
    {
        return trim((string) ($this->validated('q') ?? ''));
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $data = $this->validated();

        return [
            'category' => $data['category'] ?? null,
            'brand' => $data['brand'] ?? null,
            'shop' => $data['shop'] ?? null,
            'price_min' => $this->minor($data['price_min'] ?? null),
            'price_max' => $this->minor($data['price_max'] ?? null),
            'availability' => $data['availability'] ?? null,
            'condition' => $data['condition'] ?? null,
            'sort' => $data['sort'] ?? 'relevance',
        ];
    }

    private function minor(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Money::toMinor((string) $value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
