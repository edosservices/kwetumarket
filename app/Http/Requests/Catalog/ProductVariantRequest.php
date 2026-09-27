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
    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name'));

        if ($name === '') {
            $name = trim(implode(' / ', array_filter([
                trim((string) $this->input('color_name')),
                trim((string) $this->input('size')),
            ])));
        }

        if ($name !== '') {
            $this->merge(['name' => $name]);
        }
    }

    public function rules(): array
    {
        $variant = $this->route('variant');
        $variantId = $variant instanceof ProductVariant ? $variant->id : null;
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/', Rule::unique('product_variants', 'sku')->ignore($variantId), Rule::unique('products', 'sku')],
            'price' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'promotional_price' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(VariantStatus::values())],
            'color_name' => ['nullable', 'string', 'max:40'],
            'color_hex' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'size' => ['nullable', 'string', 'max:20'],
            'weight' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'material' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'capacity' => ['nullable', 'string', 'max:40'],
            'version' => ['nullable', 'string', 'max:40'],
            'image_id' => ['nullable', 'integer', Rule::exists('product_images', 'id')->where('product_id', $product instanceof Product ? $product->id : 0)],
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

        if ($this->filled('color_name')) {
            $attributes['color'] = (string) $this->input('color_name');
        }

        if ($this->filled('size')) {
            $attributes['size'] = (string) $this->input('size');
        }

        return [
            'name' => (string) $this->input('name'),
            'sku' => (string) $this->input('sku'),
            'color_name' => $this->filled('color_name') ? (string) $this->input('color_name') : null,
            'color_hex' => $this->filled('color_hex') ? strtoupper((string) $this->input('color_hex')) : null,
            'size' => $this->filled('size') ? (string) $this->input('size') : null,
            'weight' => $this->filled('weight') ? $this->integer('weight') : null,
            'material' => $this->filled('material') ? (string) $this->input('material') : null,
            'model' => $this->filled('model') ? (string) $this->input('model') : null,
            'capacity' => $this->filled('capacity') ? (string) $this->input('capacity') : null,
            'version' => $this->filled('version') ? (string) $this->input('version') : null,
            'price' => $this->filled('price') ? Money::toMinor((string) $this->input('price')) : null,
            'promotional_price' => $this->filled('promotional_price') ? Money::toMinor((string) $this->input('promotional_price')) : null,
            'image_id' => $this->filled('image_id') ? (int) $this->input('image_id') : null,
            'status' => VariantStatus::from((string) $this->input('status')),
            'attributes' => $attributes,
        ];
    }
}
