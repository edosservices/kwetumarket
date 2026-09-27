<?php

namespace App\Support\Rbac;

use App\Enums\UserRole;

class PermissionMatrix
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.suspend',
            'vendors.view', 'vendors.create', 'vendors.edit', 'vendors.approve', 'vendors.suspend',
            'shops.view', 'shops.create', 'shops.edit', 'shops.approve', 'shops.suspend',
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.approve', 'products.publish',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'orders.view', 'orders.create', 'orders.edit', 'orders.cancel', 'orders.refund', 'orders.process',
            'inventory.view', 'inventory.manage',
            'delivery.view', 'delivery.assign', 'delivery.update', 'delivery.manage',
            'finance.view', 'finance.reports', 'finance.payouts', 'finance.refunds', 'finance.commissions',
            'analytics.view', 'analytics.sales', 'analytics.customers', 'analytics.vendors', 'analytics.products', 'analytics.delivery',
            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.publish',
            'support.view', 'support.manage',
            'settings.view', 'settings.edit',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.manage',
            'audit.view',
        ];
    }

    /**
     * @return list<string>
     */
    public static function for(UserRole $role): array
    {
        return match ($role) {
            UserRole::SuperAdmin => self::all(),
            UserRole::AdminManager => [
                'users.view', 'users.create', 'users.edit', 'users.suspend',
                'vendors.view', 'vendors.edit', 'vendors.approve', 'vendors.suspend',
                'shops.view', 'shops.approve', 'shops.suspend',
                'analytics.view', 'analytics.customers', 'analytics.vendors',
                'support.view', 'support.manage',
                'roles.view',
                'audit.view',
            ],
            UserRole::CatalogManager => [
                'products.view', 'products.edit', 'products.approve', 'products.publish', 'products.delete',
                'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                'shops.view', 'vendors.view',
                'analytics.view', 'analytics.products',
            ],
            UserRole::OrderManager => [
                'orders.view', 'orders.edit', 'orders.cancel', 'orders.process',
                'delivery.view',
                'support.view',
            ],
            UserRole::FinanceManager => [
                'finance.view', 'finance.reports', 'finance.payouts', 'finance.refunds', 'finance.commissions',
                'orders.view', 'orders.refund',
                'analytics.view', 'analytics.sales',
            ],
            UserRole::SupportAgent => [
                'support.view', 'support.manage',
                'orders.view',
                'users.view',
            ],
            UserRole::MarketingManager => [
                'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.publish',
                'products.view',
                'analytics.view',
            ],
            UserRole::Moderator => [
                'products.view', 'products.approve',
                'shops.view', 'shops.approve',
                'vendors.view',
                'support.view',
            ],
            UserRole::Vendor => [
                'shops.view', 'shops.edit',
                'products.view', 'products.create', 'products.edit', 'products.delete',
                'inventory.view', 'inventory.manage',
                'orders.view', 'orders.process',
                'finance.view',
                'marketing.view', 'marketing.create', 'marketing.edit',
                'analytics.view', 'analytics.sales', 'analytics.products',
                'support.view',
            ],
            UserRole::VendorManager => [
                'shops.view', 'shops.edit',
                'products.view', 'products.create', 'products.edit',
                'inventory.view', 'inventory.manage',
                'orders.view', 'orders.process',
                'analytics.view', 'analytics.sales',
            ],
            UserRole::VendorCatalogManager => [
                'products.view', 'products.create', 'products.edit',
                'inventory.view', 'inventory.manage',
                'categories.view',
            ],
            UserRole::VendorOrderManager => [
                'orders.view', 'orders.process',
                'inventory.view',
            ],
            UserRole::DeliveryManager => [
                'delivery.view', 'delivery.assign', 'delivery.update', 'delivery.manage',
                'analytics.view', 'analytics.delivery',
                'users.view',
            ],
            UserRole::DeliveryAgent => [
                'delivery.view', 'delivery.update',
            ],
            UserRole::Customer => [
                'orders.view', 'orders.create',
                'support.view',
            ],
        };
    }
}
