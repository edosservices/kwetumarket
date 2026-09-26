<?php

namespace App\Http\Requests;

use App\Support\NearbyQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NearbyRequest extends FormRequest
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
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric'],
            'sort' => ['nullable', 'string', Rule::in(NearbyQuery::sorts())],
        ];
    }

    public function latitude(): float
    {
        return (float) $this->input('lat');
    }

    public function longitude(): float
    {
        return (float) $this->input('lng');
    }

    public function radiusKm(): float
    {
        return NearbyQuery::radius($this->input('radius'));
    }

    public function sortBy(string $default = 'nearest'): string
    {
        return NearbyQuery::sort($this->input('sort'), $default);
    }
}
