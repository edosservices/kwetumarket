<?php

namespace Database\Factories;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
            'brand_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'sku' => 'TW-'.fake()->unique()->bothify('??-#####'),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(500, 250000) * 100,
            'compare_at_price' => null,
            'currency' => 'CDF',
            'status' => ProductStatus::Draft,
            'condition' => ProductCondition::New,
            'weight' => fake()->optional()->numberBetween(100, 5000),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductStatus::Published,
            'published_at' => now(),
        ]);
    }
}
