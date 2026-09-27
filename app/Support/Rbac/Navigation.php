<?php

namespace App\Support\Rbac;

use App\Enums\AccountArea;
use App\Models\User;

class Navigation
{
    /**
     * @return list<array{label: string, href: string, active: bool}>
     */
    public static function for(User $user, AccountArea $area): array
    {
        $items = match ($area) {
            AccountArea::Admin => self::admin(),
            AccountArea::Vendor => self::vendor(),
            AccountArea::Delivery => self::delivery(),
            AccountArea::Customer => self::customer(),
        };

        return array_values(array_filter($items, function (array $item) use ($user): bool {
            return $item['permission'] === null || $user->can($item['permission']);
        }));
    }

    /**
     * @return list<array{label: string, href: string, active: bool, permission: ?string}>
     */
    private static function admin(): array
    {
        return [
            self::item('ui.nav.dashboard', route('admin.dashboard'), 'admin.dashboard', null),
            self::item('ui.modules.users', route('admin.module', 'utilisateurs'), 'admin.module', 'users.view', 'utilisateurs'),
            self::item('ui.modules.vendors', route('admin.module', 'vendeurs'), 'admin.module', 'vendors.view', 'vendeurs'),
            self::item('ui.modules.shops', route('admin.module', 'boutiques'), 'admin.module', 'shops.view', 'boutiques'),
            self::item('ui.modules.products', route('admin.module', 'produits'), 'admin.module', 'products.view', 'produits'),
            self::item('ui.modules.categories', route('admin.module', 'categories'), 'admin.module', 'categories.view', 'categories'),
            self::item('ui.modules.brands', route('admin.module', 'marques'), 'admin.module', 'categories.view', 'marques'),
            self::item('ui.modules.orders', route('admin.module', 'commandes'), 'admin.module', 'orders.view', 'commandes'),
            self::item('ui.modules.delivery', route('admin.module', 'livraisons'), 'admin.module', 'delivery.view', 'livraisons'),
            self::item('ui.modules.finance', route('admin.module', 'finances'), 'admin.module', 'finance.view', 'finances'),
            self::item('ui.modules.marketing', route('admin.module', 'marketing'), 'admin.module', 'marketing.view', 'marketing'),
            self::item('ui.modules.analytics', route('admin.module', 'analytique'), 'admin.module', 'analytics.view', 'analytique'),
            self::item('ui.modules.support', route('admin.module', 'support'), 'admin.module', 'support.view', 'support'),
            self::item('ui.modules.settings', route('admin.module', 'parametres'), 'admin.module', 'settings.view', 'parametres'),
            self::item('ui.modules.roles', route('admin.module', 'roles'), 'admin.module', 'roles.view', 'roles'),
            self::item('ui.modules.permissions', route('admin.module', 'permissions'), 'admin.module', 'permissions.view', 'permissions'),
            self::item('ui.modules.audit', route('admin.module', 'audit'), 'admin.module', 'audit.view', 'audit'),
            self::item('ui.modules.notifications', route('notifications.index'), 'notifications.index', null),
        ];
    }

    /**
     * @return list<array{label: string, href: string, active: bool, permission: ?string}>
     */
    private static function vendor(): array
    {
        return [
            self::item('ui.nav.dashboard', route('vendor.dashboard'), 'vendor.dashboard', null),
            self::item('ui.modules.shop', route('vendor.module', 'boutique'), 'vendor.module', 'shops.view', 'boutique'),
            self::item('ui.modules.products', route('vendor.products.index'), 'vendor.products.*', 'products.view'),
            self::item('ui.modules.stock', route('vendor.module', 'stock'), 'vendor.module', 'inventory.view', 'stock'),
            self::item('ui.modules.orders', route('vendor.module', 'commandes'), 'vendor.module', 'orders.view', 'commandes'),
            self::item('ui.modules.customers', route('vendor.module', 'clients'), 'vendor.module', 'orders.view', 'clients'),
            self::item('ui.modules.finance', route('vendor.module', 'finances'), 'vendor.module', 'finance.view', 'finances'),
            self::item('ui.modules.marketing', route('vendor.module', 'promotions'), 'vendor.module', 'marketing.view', 'promotions'),
            self::item('ui.modules.analytics', route('vendor.module', 'statistiques'), 'vendor.module', 'analytics.view', 'statistiques'),
            self::item('ui.modules.messages', route('vendor.module', 'messages'), 'vendor.module', null, 'messages'),
            self::item('ui.modules.support', route('vendor.module', 'support'), 'vendor.module', 'support.view', 'support'),
            self::item('ui.modules.notifications', route('notifications.index'), 'notifications.index', null),
        ];
    }

    /**
     * @return list<array{label: string, href: string, active: bool, permission: ?string}>
     */
    private static function delivery(): array
    {
        return [
            self::item('ui.nav.dashboard', route('delivery.dashboard'), 'delivery.dashboard', null),
            self::item('ui.modules.missions', route('delivery.missions'), 'delivery.missions', 'delivery.view'),
            self::item('ui.modules.agents', route('delivery.module', 'livreurs'), 'delivery.module', 'delivery.manage', 'livreurs'),
            self::item('ui.modules.assignments', route('delivery.module', 'affectations'), 'delivery.module', 'delivery.assign', 'affectations'),
            self::item('ui.modules.zones', route('delivery.module', 'zones'), 'delivery.module', 'delivery.manage', 'zones'),
            self::item('ui.modules.problems', route('delivery.module', 'problemes'), 'delivery.module', 'delivery.manage', 'problemes'),
            self::item('ui.modules.reports', route('delivery.module', 'rapports'), 'delivery.module', 'analytics.delivery', 'rapports'),
            self::item('ui.modules.earnings', route('delivery.module', 'revenus'), 'delivery.module', 'delivery.update', 'revenus'),
            self::item('ui.modules.messages', route('delivery.module', 'messages'), 'delivery.module', null, 'messages'),
            self::item('ui.modules.notifications', route('notifications.index'), 'notifications.index', null),
        ];
    }

    /**
     * @return list<array{label: string, href: string, active: bool, permission: ?string}>
     */
    private static function customer(): array
    {
        return [
            self::item('ui.nav.dashboard', route('customer.dashboard'), 'customer.dashboard', null),
            self::item('ui.nav.profile', route('profile.edit'), 'profile.edit', null),
            self::item('ui.modules.orders', route('customer.orders'), 'customer.orders', 'orders.view'),
            self::item('ui.modules.addresses', route('customer.module', 'adresses'), 'customer.module', null, 'adresses'),
            self::item('ui.modules.wishlist', route('customer.module', 'favoris'), 'customer.module', null, 'favoris'),
            self::item('ui.modules.follows', route('customer.module', 'boutiques'), 'customer.module', null, 'boutiques'),
            self::item('ui.modules.reviews', route('customer.module', 'avis'), 'customer.module', null, 'avis'),
            self::item('ui.modules.messages', route('customer.module', 'messages'), 'customer.module', null, 'messages'),
            self::item('ui.modules.notifications', route('notifications.index'), 'notifications.index', null),
            self::item('ui.modules.support', route('customer.module', 'support'), 'customer.module', 'support.view', 'support'),
            self::item('ui.nav.cart', route('cart.show'), 'cart.show', null),
        ];
    }

    /**
     * @return array{label: string, href: string, active: bool, permission: ?string}
     */
    private static function item(string $label, string $href, string $route, ?string $permission, ?string $module = null): array
    {
        $active = $module === null
            ? request()->routeIs($route)
            : request()->routeIs($route) && request()->route('module') === $module;

        return [
            'label' => __($label),
            'href' => $href,
            'active' => $active,
            'permission' => $permission,
        ];
    }
}
