<?php

namespace Database\Factories;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Market';

        return [
            'vendor_id' => Vendor::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(12),
            'logo' => null,
            'cover_image' => null,
            'phone' => '+2438'.fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'location' => fake()->randomElement(['Gombe, Kinshasa', 'Lingwala, Kinshasa', 'Lubumbashi', 'Goma']),
            'status' => ShopStatus::Active,
            'address_id' => null,
        ];
    }
}
