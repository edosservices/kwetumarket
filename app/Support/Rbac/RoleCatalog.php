<?php

namespace App\Support\Rbac;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCatalog
{
    /**
     * @return Collection<int, Role>
     */
    public static function roles(): Collection
    {
        return Role::query()->with('permissions')->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Permission>
     */
    public static function permissions(): Collection
    {
        return Permission::query()->with('roles')->orderBy('name')->get();
    }

    /**
     * @return Collection<int, User>
     */
    public static function holders(): Collection
    {
        return User::query()->with('roles.permissions')->orderBy('name')->get();
    }

    public static function description(string $role): string
    {
        $key = 'ui.role_descriptions.'.$role;

        return UserRole::tryFrom($role) ? __($key) : $role;
    }
}
