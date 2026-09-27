<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $client = [
            'cart.manage',
            'orders.create',
            'orders.view-own',
            'addresses.manage',
            'wishlist.manage',
            'reviews.create',
            'messages.create',
        ];

        $vendor = [
            ...$client,
            'shops.manage-own',
            'products.manage-own',
            'inventory.manage-own',
            'orders.manage-own',
            'promotions.manage-own',
            'wallet.view-own',
            'withdrawals.request',
        ];

        $delivery = [
            'deliveries.view-assigned',
            'deliveries.update',
            'wallet.view-own',
        ];

        $admin = [
            ...$vendor,
            ...$delivery,
            'users.manage',
            'vendors.manage',
            'shops.manage',
            'categories.manage',
            'products.manage',
            'orders.manage',
            'payments.manage',
            'transactions.view',
            'commissions.manage',
            'withdrawals.manage',
            'deliveries.manage',
            'promotions.manage',
            'advertisements.manage',
            'disputes.manage',
            'settings.manage',
            'statistics.view',
            'audit.view',
        ];

        $matrix = [
            UserRole::Client->value => $client,
            UserRole::Vendor->value => $vendor,
            UserRole::DeliveryAgent->value => $delivery,
            UserRole::Admin->value => array_values(array_unique($admin)),
        ];

        $names = array_values(array_unique(array_merge(...array_values($matrix))));

        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach ($matrix as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
