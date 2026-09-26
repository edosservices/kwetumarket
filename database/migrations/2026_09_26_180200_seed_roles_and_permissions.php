<?php

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new RoleAndPermissionSeeder)->run();
    }

    public function down(): void
    {
        // Les rôles sont des données de référence. La suppression des tables
        // de permissions est portée par la migration du package.
    }
};
