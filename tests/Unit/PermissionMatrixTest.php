<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Support\Rbac\PermissionMatrix;
use PHPUnit\Framework\TestCase;

class PermissionMatrixTest extends TestCase
{
    public function test_the_catalog_matches_the_granular_matrix(): void
    {
        $permissions = PermissionMatrix::all();

        $this->assertCount(63, $permissions);
        $this->assertSame($permissions, array_values(array_unique($permissions)));
        $this->assertContains('users.suspend', $permissions);
        $this->assertContains('delivery.assign', $permissions);
        $this->assertContains('permissions.manage', $permissions);
        $this->assertContains('audit.view', $permissions);
    }

    public function test_super_admin_holds_every_permission_and_customers_do_not(): void
    {
        $this->assertSame(PermissionMatrix::all(), PermissionMatrix::for(UserRole::SuperAdmin));
        $this->assertNotContains('users.view', PermissionMatrix::for(UserRole::Customer));
        $this->assertNotContains('finance.view', PermissionMatrix::for(UserRole::VendorCatalogManager));
        $this->assertNotContains('delivery.manage', PermissionMatrix::for(UserRole::DeliveryAgent));
        $this->assertContains('products.view', PermissionMatrix::for(UserRole::CatalogManager));
        $this->assertNotContains('finance.view', PermissionMatrix::for(UserRole::CatalogManager));
    }
}
