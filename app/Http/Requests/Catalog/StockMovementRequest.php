<?php

namespace App\Http\Requests\Catalog;

use App\Enums\StockMovementType;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        if (! $product instanceof Product && $this->filled('product_id')) {
            $product = Product::query()->find($this->integer('product_id'));
        }

        return $product instanceof Product && $this->user()?->can('update', $product) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(StockMovementType::values())],
            'quantity' => ['required', 'integer', 'not_in:0', 'between:-1000000,1000000'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'reference' => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
