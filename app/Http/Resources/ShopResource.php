<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
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
            'description' => $this->description,
            'logo' => $this->mediaUrl($this->logo),
            'cover_image' => $this->mediaUrl($this->cover_image),
            'phone' => $this->phone,
            'email' => $this->email,
            'location' => $this->location,
            'address' => $this->when((bool) $this->publish_address, fn () => $this->publicAddress()),
            'latitude' => $this->when((bool) $this->publish_location && $this->latitude !== null, fn () => (float) $this->latitude),
            'longitude' => $this->when((bool) $this->publish_location && $this->longitude !== null, fn () => (float) $this->longitude),
            'opening_hours' => $this->opening_hours,
            'status' => $this->status?->value,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
