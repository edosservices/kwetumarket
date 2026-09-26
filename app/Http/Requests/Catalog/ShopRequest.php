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
        foreach ([
            'slug', 'latitude', 'longitude', 'country', 'province', 'city', 'commune', 'quarter',
            'address_line', 'opening_hours', 'business_name', 'manager_name',
            'whatsapp', 'instagram', 'tiktok', 'facebook', 'website',
        ] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
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

        if (! $shop instanceof Shop) {
            $shop = $this->user()?->vendorProfile?->shops()->first();
        }

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
            'country' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'commune' => ['nullable', 'string', 'max:80'],
            'quarter' => ['nullable', 'string', 'max:80'],
            'address_line' => ['nullable', 'string', 'max:180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],
            'publish_location' => ['sometimes', 'boolean'],
            'publish_address' => ['sometimes', 'boolean'],
            'business_name' => ['nullable', 'string', 'max:140'],
            'manager_name' => ['nullable', 'string', 'max:140'],
            'whatsapp' => ['nullable', 'string', 'max:160', 'regex:/^(\+?[0-9]{8,15}|https:\/\/\S+)$/'],
            'instagram' => ['nullable', 'string', 'max:160', 'regex:/^(@?[A-Za-z0-9._]{1,80}|https:\/\/\S+)$/'],
            'tiktok' => ['nullable', 'string', 'max:160', 'regex:/^(@?[A-Za-z0-9._]{1,80}|https:\/\/\S+)$/'],
            'facebook' => ['nullable', 'string', 'max:160', 'regex:/^(@?[A-Za-z0-9._]{1,80}|https:\/\/\S+)$/'],
            'website' => ['nullable', 'string', 'max:255', 'regex:/^https:\/\/\S+$/'],
            'status' => ['required', Rule::in($statuses)],
            'logo' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,gif', 'extensions:jpg,jpeg,png,webp,gif'],
            'cover_image' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,gif', 'extensions:jpg,jpeg,png,webp,gif'],
        ];
    }
}
