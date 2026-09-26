<?php

namespace App\Http\Requests;

use App\Support\NearbyQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImageSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'max:'.(int) config('twende.vision.max_kilobytes'),
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
            ],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric'],
            'sort' => ['nullable', 'string', Rule::in(NearbyQuery::sorts())],
        ];
    }
}
