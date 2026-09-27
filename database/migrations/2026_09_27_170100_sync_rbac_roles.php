<?php

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameRole('client', 'customer');
        $this->renameRole('admin', 'super_admin');

        (new RoleAndPermissionSeeder)->run();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function renameRole(string $from, string $to): void
    {
        $current = Role::query()->where('name', $from)->where('guard_name', 'web')->first();
        $target = Role::query()->where('name', $to)->where('guard_name', 'web')->first();

        if ($current === null) {
            return;
        }

        if ($target === null) {
            $current->name = $to;
            $current->save();

            return;
        }

        $assigned = DB::table('model_has_roles')->where('role_id', $current->id)->get();

        foreach ($assigned as $row) {
            $exists = DB::table('model_has_roles')
                ->where('role_id', $target->id)
                ->where('model_type', $row->model_type)
                ->where('model_id', $row->model_id)
                ->exists();

            if ($exists) {
                DB::table('model_has_roles')
                    ->where('role_id', $current->id)
                    ->where('model_type', $row->model_type)
                    ->where('model_id', $row->model_id)
                    ->delete();
            } else {
                DB::table('model_has_roles')
                    ->where('role_id', $current->id)
                    ->where('model_type', $row->model_type)
                    ->where('model_id', $row->model_id)
                    ->update(['role_id' => $target->id]);
            }
        }
    }
};
