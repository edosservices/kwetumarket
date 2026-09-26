<?php

namespace App\Http\Requests\Commerce;

use App\Services\Payments\PaymentCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.create') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['nullable', 'integer'],
            'phone' => ['required_without:address_id', 'nullable', 'string', 'max:32'],
            'country' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:120'],
            'city' => ['required_without:address_id', 'nullable', 'string', 'max:120'],
            'commune' => ['nullable', 'string', 'max:120'],
            'quarter' => ['nullable', 'string', 'max:120'],
            'address' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'delivery_zone_id' => ['required', 'integer', Rule::exists('delivery_zones', 'id')->where('is_active', true)],
            'payment_method' => ['required', Rule::in(app(PaymentCatalog::class)->codes())],
            'notes' => ['nullable', 'string', 'max:1000'],
            'coupon' => ['nullable', 'string', 'max:32'],
            'save_address' => ['nullable', 'boolean'],
        ];
    }
}
