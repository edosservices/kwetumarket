<?php

namespace App\Http\Requests\Catalog;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shop = $this->route('shop');

        if ($shop instanceof Shop) {
            return $this->user()?->can('update', $shop) === true;
        }

        return $this->user()?->can('create', Shop::class) === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('slug') === '') {
            $this->merge(['slug' => null]);
        }

        if ($this->filled('phone')) {
            $this->merge([
                'phone' => PhoneNumber::normalize((string) $this->input('phone')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $shop = $this->route('shop');
        $shopId = $shop instanceof Shop ? $shop->id : null;
        $statuses = $this->user()?->can('shops.manage')
            ? ShopStatus::values()
            : [ShopStatus::Pending->value, ShopStatus::Active->value];

        return [
            'name' => ['required', 'string', 'max:140'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('shops', 'slug')->ignore($shopId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'location' => ['nullable', 'string', 'max:180'],
            'status' => ['required', Rule::in($statuses)],
            'logo' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,gif', 'extensions:jpg,jpeg,png,webp,gif'],
            'cover_image' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,gif', 'extensions:jpg,jpeg,png,webp,gif'],
        ];
    }
}
