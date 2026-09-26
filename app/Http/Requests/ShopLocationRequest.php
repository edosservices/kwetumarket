<?php

namespace App\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;

class ShopLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shop = $this->route('shop');

        return $shop instanceof Shop && $this->user()?->can('update', $shop) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
