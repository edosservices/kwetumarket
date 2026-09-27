<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Support\Rbac\PermissionMatrix;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionMatrix::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (UserRole::cases() as $role) {
            $record = Role::findOrCreate($role->value, 'web');
            $record->syncPermissions(PermissionMatrix::for($role));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
