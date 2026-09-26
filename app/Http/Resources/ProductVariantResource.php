<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'attributes' => $this->attributes,
            'price' => $this->price,
            'price_formatted' => $this->price === null ? null : Money::format((int) $this->price, $this->product->currency ?? 'CDF'),
            'stock' => $this->stock,
            'status' => $this->status->value,
        ];
    }
}
