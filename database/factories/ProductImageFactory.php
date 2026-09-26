<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'disk' => 'public',
            'path' => 'products/'.Str::uuid().'.jpg',
            'alt_text' => fake()->optional()->sentence(3),
            'is_primary' => true,
            'sort_order' => 0,
        ];
    }
}
