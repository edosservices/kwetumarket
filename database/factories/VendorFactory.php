<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withRole(UserRole::Vendor),
            'status' => VendorStatus::Active,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => VendorStatus::Pending,
        ]);
    }
}
