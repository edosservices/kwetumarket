<?php

namespace App\Http\Requests\Catalog;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Shop;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['brand_id', 'slug', 'compare_at_price', 'weight', 'initial_stock', 'supplier_name'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function authorize(): bool
    {
        $product = $this->route('product');

        if ($product instanceof Product) {
            return $this->user()?->can('update', $product) === true;
        }

        return $this->user()?->can('create', Product::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : null;
        $user = $this->user();
        $statuses = $user?->can('products.manage')
            ? ProductStatus::values()
            : [ProductStatus::Draft->value, ProductStatus::Pending->value, ProductStatus::Archived->value];

        return [
            'shop_id' => [
                'required',
                'integer',
                Rule::exists('shops', 'id')->where(function ($query) use ($user): void {
                    if ($user?->can('products.manage')) {
                        return;
                    }

                    $vendorId = $user?->vendorProfile?->id;
                    $query->where('vendor_id', $vendorId ?? 0);
                }),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['required', 'string', 'max:10000'],
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'compare_at_price' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'currency' => ['required', Rule::in(config('twende.currencies'))],
            'status' => ['required', Rule::in($statuses)],
            'condition' => ['required', Rule::in(ProductCondition::values())],
            'weight' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'initial_stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_dropship' => ['sometimes', 'boolean'],
            'supplier_name' => ['nullable', 'string', 'max:160'],
            'supplier_sku' => ['nullable', 'string', 'max:64'],
            'supplier_price' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $price = Money::toMinor((string) $this->input('price'));
            $compare = $this->filled('compare_at_price')
                ? Money::toMinor((string) $this->input('compare_at_price'))
                : null;

            if ($compare !== null && $compare <= $price) {
                $validator->errors()->add('compare_at_price', __('ui.catalog.compare_price_invalid'));
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        $product = $this->route('product');
        $status = ProductStatus::from((string) $this->input('status'));
        $publishedAt = $product instanceof Product ? $product->published_at : null;

        return [
            'shop_id' => $this->integer('shop_id'),
            'category_id' => $this->integer('category_id'),
            'brand_id' => $this->filled('brand_id') ? $this->integer('brand_id') : null,
            'name' => (string) $this->input('name'),
            'slug' => $this->filled('slug')
                ? (string) $this->input('slug')
                : ($product instanceof Product ? $product->slug : null),
            'sku' => (string) $this->input('sku'),
            'description' => (string) $this->input('description'),
            'price' => Money::toMinor((string) $this->input('price')),
            'compare_at_price' => $this->filled('compare_at_price') ? Money::toMinor((string) $this->input('compare_at_price')) : null,
            'currency' => (string) $this->input('currency'),
            'status' => $status,
            'condition' => ProductCondition::from((string) $this->input('condition')),
            'weight' => $this->filled('weight') ? $this->integer('weight') : null,
            'is_dropship' => $this->exists('is_dropship')
                ? $this->boolean('is_dropship')
                : (bool) ($product instanceof Product ? $product->is_dropship : false),
            'supplier_name' => $this->boolean('is_dropship') ? ($this->filled('supplier_name') ? (string) $this->input('supplier_name') : null) : null,
            'supplier_sku' => $this->boolean('is_dropship') ? ($this->filled('supplier_sku') ? (string) $this->input('supplier_sku') : null) : null,
            'supplier_price' => $this->boolean('is_dropship') && $this->filled('supplier_price') ? Money::toMinor((string) $this->input('supplier_price')) : null,
            'published_at' => $status === ProductStatus::Published ? ($publishedAt ?? now()) : null,
        ];
    }
}
