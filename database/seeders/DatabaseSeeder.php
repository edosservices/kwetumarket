<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const DEMO_PASSWORD = 'Twende-Demo-2026';

    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        if (app()->environment('production')) {
            return;
        }

        $this->demoUser('Admin Twende', 'admin@twende.market', '+243900000001', UserRole::Admin);
        $this->demoUser('Grâce Ilunga', 'client@twende.market', '+243900000002', UserRole::Client);
        $this->demoUser('Patrick Mbuyi', 'vendeur@twende.market', '+243900000003', UserRole::Vendor);
        $this->demoUser('Sarah Ngalula', 'livreur@twende.market', '+243900000004', UserRole::DeliveryAgent);
    }

    private function demoUser(string $name, string $email, string $phone, UserRole $role): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => self::DEMO_PASSWORD,
                'locale' => 'fr',
                'currency' => 'CDF',
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role->value]);
    }
}
