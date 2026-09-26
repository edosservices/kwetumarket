<?php

namespace Database\Factories;

use App\Enums\VariantStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-'.fake()->unique()->bothify('??-#####'),
            'name' => fake()->randomElement(['Noir', 'Bleu', 'Blanc']).' / '.fake()->randomElement(['128 GB', '256 GB']),
            'attributes' => [
                'color' => 'Noir',
                'capacity' => '128 GB',
            ],
            'price' => null,
            'stock' => 0,
            'image_id' => null,
            'status' => VariantStatus::Active,
        ];
    }
}
