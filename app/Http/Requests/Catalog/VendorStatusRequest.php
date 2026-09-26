<?php

namespace App\Http\Requests\Catalog;

use App\Enums\VendorStatus;
use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Vendor::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [$this->routeIs('admin.vendors.store') ? 'required' : 'exclude', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(VendorStatus::values())],
        ];
    }
}
