<?php

namespace App\Http\Requests\Catalog;

use App\Enums\CatalogStatus;
use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['slug', 'description'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function authorize(): bool
    {
        $brand = $this->route('brand');

        if ($brand instanceof Brand) {
            return $this->user()?->can('update', $brand) === true;
        }

        return $this->user()?->can('create', Brand::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = $brand instanceof Brand ? $brand->id : null;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('brands', 'slug')->ignore($brandId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,gif', 'extensions:jpg,jpeg,png,webp,gif'],
            'status' => ['required', Rule::in(CatalogStatus::values())],
        ];
    }
}
