<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->when($request->routeIs('api.v1.products.show', 'api.v1.vendor.products.show', 'api.v1.admin.products.show'), $this->description),
            'price' => $this->price,
            'price_formatted' => Money::format((int) $this->price, $this->currency),
            'compare_at_price' => $this->compare_at_price,
            'currency' => $this->currency,
            'discount_percent' => $this->discountPercent(),
            'status' => $this->status->value,
            'condition' => $this->condition->value,
            'available' => $this->availableQuantity(),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => $this->when($this->relationLoaded('brand') && $this->brand, fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
            'image' => new ProductImageResource($this->whenLoaded('primaryImage')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
