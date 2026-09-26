<?php

namespace App\Http\Requests\Catalog;

use App\Enums\VariantStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product && $this->user()?->can('update', $product) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $variant = $this->route('variant');
        $variantId = $variant instanceof ProductVariant ? $variant->id : null;

        return [
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/', Rule::unique('product_variants', 'sku')->ignore($variantId), Rule::unique('products', 'sku')],
            'price' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(VariantStatus::values())],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['nullable', 'string', 'max:80'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function variantAttributes(): array
    {
        $attributes = collect($this->input('attributes', []))
            ->map(fn ($value) => is_string($value) ? trim($value) : '')
            ->filter(fn (string $value) => $value !== '')
            ->all();

        return [
            'name' => (string) $this->input('name'),
            'sku' => (string) $this->input('sku'),
            'price' => $this->filled('price') ? Money::toMinor((string) $this->input('price')) : null,
            'status' => VariantStatus::from((string) $this->input('status')),
            'attributes' => $attributes,
        ];
    }
}
