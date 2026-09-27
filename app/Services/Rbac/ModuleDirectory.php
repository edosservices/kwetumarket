<?php

namespace App\Services\Rbac;

use App\Enums\AccountArea;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Commission;
use App\Models\Delivery;
use App\Models\DeliveryZone;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\ShopFollow;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Support\Money;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModuleDirectory
{
    /**
     * @return array{title: string, columns: list<string>, rows: list<array{cells: list<string>, suspend_user_id?: int}>}
     */
    public function open(User $user, AccountArea $area, string $module): array
    {
        $permission = $this->permission($area, $module);

        abort_if($permission === false, 404);
        abort_if(is_string($permission) && ! $user->can($permission), 403);

        $rows = match ($area) {
            AccountArea::Admin => $this->admin($module),
            AccountArea::Vendor => $this->vendor($user, $module),
            AccountArea::Delivery => $this->delivery($user, $module),
            AccountArea::Customer => $this->customer($user, $module),
        };

        return [
            'title' => __('ui.modules.'.$this->labelKey($module)),
            'columns' => $rows['columns'],
            'rows' => $rows['rows'],
            'module' => $module,
        ];
    }

    /**
     * @return list<string>
     */
    public function keys(AccountArea $area): array
    {
        return array_keys($this->map()[$area->value]);
    }

    private function permission(AccountArea $area, string $module): string|false|null
    {
        $map = $this->map()[$area->value] ?? null;

        if (! is_array($map) || ! array_key_exists($module, $map)) {
            return false;
        }

        return $map[$module];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    private function map(): array
    {
        return [
            AccountArea::Admin->value => [
                'utilisateurs' => 'users.view',
                'vendeurs' => 'vendors.view',
                'boutiques' => 'shops.view',
                'produits' => 'products.view',
                'categories' => 'categories.view',
                'marques' => 'categories.view',
                'commandes' => 'orders.view',
                'livraisons' => 'delivery.view',
                'finances' => 'finance.view',
                'marketing' => 'marketing.view',
                'analytique' => 'analytics.view',
                'support' => 'support.view',
                'parametres' => 'settings.view',
                'roles' => 'roles.view',
                'permissions' => 'permissions.view',
                'audit' => 'audit.view',
            ],
            AccountArea::Vendor->value => [
                'boutique' => 'shops.view',
                'stock' => 'inventory.view',
                'commandes' => 'orders.view',
                'clients' => 'orders.view',
                'finances' => 'finance.view',
                'promotions' => 'marketing.view',
                'statistiques' => 'analytics.view',
                'messages' => null,
                'support' => 'support.view',
            ],
            AccountArea::Delivery->value => [
                'livreurs' => 'delivery.manage',
                'affectations' => 'delivery.assign',
                'zones' => 'delivery.manage',
                'problemes' => 'delivery.manage',
                'rapports' => 'analytics.delivery',
                'revenus' => 'delivery.update',
                'messages' => null,
            ],
            AccountArea::Customer->value => [
                'adresses' => null,
                'favoris' => null,
                'boutiques' => null,
                'avis' => null,
                'messages' => null,
                'support' => 'support.view',
            ],
        ];
    }

    private function labelKey(string $module): string
    {
        return match ($module) {
            'utilisateurs' => 'users',
            'vendeurs' => 'vendors',
            'boutiques' => 'shops',
            'produits' => 'products',
            'categories' => 'categories',
            'marques' => 'brands',
            'commandes' => 'orders',
            'livraisons', 'problemes' => 'delivery',
            'finances' => 'finance',
            'marketing', 'promotions' => 'marketing',
            'analytique', 'statistiques', 'rapports' => 'analytics',
            'support' => 'support',
            'parametres' => 'settings',
            'roles' => 'roles',
            'permissions' => 'permissions',
            'audit' => 'audit',
            'boutique' => 'shop',
            'stock' => 'stock',
            'clients' => 'customers',
            'messages' => 'messages',
            'livreurs' => 'agents',
            'affectations' => 'assignments',
            'zones' => 'zones',
            'revenus' => 'earnings',
            'adresses' => 'addresses',
            'favoris' => 'wishlist',
            'avis' => 'reviews',
            default => 'records',
        };
    }

    /**
     * @return array{columns: list<string>, rows: list<array{cells: list<string>, suspend_user_id?: int}>}
     */
    private function admin(string $module): array
    {
        return match ($module) {
            'utilisateurs' => $this->table(
                [__('ui.fields.name'), __('ui.fields.email'), __('ui.fields.role'), __('ui.fields.status')],
                User::query()->with('roles')->latest('id')->limit(50)->get()->map(fn (User $user) => [
                    'cells' => [$user->name, $user->email, $user->getRoleNames()->join(', '), $user->isSuspended() ? __('ui.fields.suspended') : __('ui.fields.active')],
                    'suspend_user_id' => $user->id,
                ]),
            ),
            'vendeurs' => $this->table(
                [__('ui.fields.name'), __('ui.fields.status')],
                Vendor::query()->latest('id')->limit(50)->get()->map(fn (Vendor $vendor) => ['cells' => [$vendor->name, $vendor->status]]),
            ),
            'boutiques' => $this->table(
                [__('ui.fields.name'), __('ui.fields.status')],
                Shop::query()->latest('id')->limit(50)->get()->map(fn (Shop $shop) => ['cells' => [$shop->name, $shop->status]]),
            ),
            'produits' => $this->table(
                [__('ui.fields.name'), __('ui.fields.status'), __('ui.fields.stock')],
                Product::query()->latest('id')->limit(50)->get()->map(fn (Product $product) => ['cells' => [$product->name, $product->status, (string) $product->stock]]),
            ),
            'categories' => $this->table([__('ui.fields.name')], Category::query()->orderBy('name')->limit(50)->get()->map(fn (Category $row) => ['cells' => [$row->name]])),
            'marques' => $this->table([__('ui.fields.name')], Brand::query()->orderBy('name')->limit(50)->get()->map(fn (Brand $row) => ['cells' => [$row->name]])),
            'commandes' => $this->table(
                [__('ui.fields.number'), __('ui.fields.status'), __('ui.fields.amount')],
                Order::query()->latest('id')->limit(50)->get()->map(fn (Order $order) => ['cells' => [$order->number, $order->status, Money::format((int) $order->total_minor, $order->currency)]]),
            ),
            'livraisons' => $this->table(
                [__('ui.fields.number'), __('ui.fields.status')],
                Delivery::query()->with('order')->latest('id')->limit(50)->get()->map(fn (Delivery $delivery) => ['cells' => [$delivery->order?->number ?? '—', $delivery->status]]),
            ),
            'finances' => $this->table(
                [__('ui.fields.kind'), __('ui.fields.amount')],
                collect([
                    ['cells' => [__('ui.stats.payments'), (string) Payment::query()->count()]],
                    ['cells' => [__('ui.stats.revenue'), Money::format((int) Payment::query()->where('status', 'successful')->sum('amount_minor'))]],
                    ['cells' => [__('ui.stats.commissions'), Money::format((int) Commission::query()->sum('amount_minor'))]],
                    ['cells' => [__('ui.stats.refunds'), Money::format((int) Refund::query()->sum('amount_minor'))]],
                    ['cells' => [__('ui.stats.payouts'), Money::format((int) Payout::query()->sum('amount_minor'))]],
                ]),
            ),
            'marketing' => $this->table(
                [__('ui.fields.name'), __('ui.fields.status')],
                Product::query()->where('status', 'published')->latest('id')->limit(20)->get()->map(fn (Product $product) => ['cells' => [$product->name, __('ui.fields.published')]]),
            ),
            'analytique' => $this->table(
                [__('ui.fields.metric'), __('ui.fields.value')],
                collect([
                    ['cells' => [__('ui.stats.orders'), (string) Order::query()->count()]],
                    ['cells' => [__('ui.stats.products'), (string) Product::query()->count()]],
                    ['cells' => [__('ui.stats.customers'), (string) User::role(UserRole::Customer->value)->count()]],
                ]),
            ),
            'support' => $this->table(
                [__('ui.fields.subject'), __('ui.fields.status')],
                SupportTicket::query()->latest('id')->limit(50)->get()->map(fn (SupportTicket $ticket) => ['cells' => [$ticket->subject, $ticket->status]]),
            ),
            'parametres' => $this->table(
                [__('ui.fields.key'), __('ui.fields.value')],
                Setting::query()->orderBy('key')->get()->map(fn (Setting $setting) => ['cells' => [$setting->key, (string) $setting->value]]),
            ),
            'roles' => $this->table(
                [__('ui.fields.role'), __('ui.fields.permissions')],
                Role::query()->withCount('permissions')->orderBy('name')->get()->map(fn (Role $role) => ['cells' => [$role->name, (string) $role->permissions_count]]),
            ),
            'permissions' => $this->table(
                [__('ui.fields.permission')],
                Permission::query()->orderBy('name')->get()->map(fn (Permission $permission) => ['cells' => [$permission->name]]),
            ),
            'audit' => $this->table(
                [__('ui.fields.action'), __('ui.fields.date')],
                AuditLog::query()->latest('id')->limit(50)->get()->map(fn (AuditLog $log) => ['cells' => [$log->action, $log->created_at?->toDateTimeString() ?? '']]),
            ),
            default => abort(404),
        };
    }

    /**
     * @return array{columns: list<string>, rows: list<array{cells: list<string>}>}
     */
    private function vendor(User $user, string $module): array
    {
        $vendorId = $user->vendorId();

        return match ($module) {
            'boutique' => $this->table(
                [__('ui.fields.name'), __('ui.fields.status')],
                Shop::query()->where('vendor_id', $vendorId)->get()->map(fn (Shop $shop) => ['cells' => [$shop->name, $shop->status]]),
            ),
            'stock' => $this->table(
                [__('ui.fields.name'), __('ui.fields.stock')],
                Product::query()->where('vendor_id', $vendorId)->orderBy('stock')->limit(50)->get()->map(fn (Product $product) => ['cells' => [$product->name, (string) $product->stock]]),
            ),
            'commandes' => $this->table(
                [__('ui.fields.number'), __('ui.fields.status'), __('ui.fields.amount')],
                Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendorId))->latest('id')->limit(50)->get()->map(fn (Order $order) => [
                    'cells' => [$order->number, $order->status, Money::format((int) $order->items()->where('vendor_id', $vendorId)->sum('line_total_minor'))],
                ]),
            ),
            'clients' => $this->table(
                [__('ui.fields.name'), __('ui.fields.orders')],
                User::query()->whereIn('id', Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendorId))->pluck('user_id'))->get()->map(fn (User $customer) => [
                    'cells' => [$customer->name, (string) Order::query()->where('user_id', $customer->id)->whereHas('items', fn ($query) => $query->where('vendor_id', $vendorId))->count()],
                ]),
            ),
            'finances' => $this->table(
                [__('ui.fields.kind'), __('ui.fields.amount')],
                collect([
                    ['cells' => [__('ui.stats.revenue'), Money::format((int) OrderItem::query()->where('vendor_id', $vendorId)->sum('line_total_minor'))]],
                    ['cells' => [__('ui.stats.commissions'), Money::format((int) Commission::query()->where('vendor_id', $vendorId)->sum('amount_minor'))]],
                    ['cells' => [__('ui.stats.payouts'), Money::format((int) Payout::query()->where('vendor_id', $vendorId)->sum('amount_minor'))]],
                ]),
            ),
            'promotions' => $this->table(
                [__('ui.fields.name'), __('ui.fields.status')],
                Product::query()->where('vendor_id', $vendorId)->where('status', 'published')->limit(20)->get()->map(fn (Product $product) => ['cells' => [$product->name, $product->status]]),
            ),
            'statistiques' => $this->table(
                [__('ui.fields.metric'), __('ui.fields.value')],
                collect([
                    ['cells' => [__('ui.stats.products'), (string) Product::query()->where('vendor_id', $vendorId)->count()]],
                    ['cells' => [__('ui.stats.orders'), (string) Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendorId))->count()]],
                ]),
            ),
            'messages' => $this->messages($user),
            'support' => $this->table(
                [__('ui.fields.subject'), __('ui.fields.status')],
                SupportTicket::query()->where('user_id', $user->id)->latest('id')->get()->map(fn (SupportTicket $ticket) => ['cells' => [$ticket->subject, $ticket->status]]),
            ),
            default => abort(404),
        };
    }

    /**
     * @return array{columns: list<string>, rows: list<array{cells: list<string>}>}
     */
    private function delivery(User $user, string $module): array
    {
        $personal = ! $user->can('delivery.manage');

        return match ($module) {
            'livreurs' => $this->table(
                [__('ui.fields.name'), __('ui.fields.email')],
                User::role(UserRole::DeliveryAgent->value)->orderBy('name')->get()->map(fn (User $agent) => ['cells' => [$agent->name, $agent->email]]),
            ),
            'affectations' => $this->table(
                [__('ui.fields.number'), __('ui.fields.status'), __('ui.fields.agent')],
                Delivery::query()->with(['order', 'agent'])->latest('id')->limit(50)->get()->map(fn (Delivery $delivery) => ['cells' => [$delivery->order?->number ?? '—', $delivery->status, $delivery->agent?->name ?? '—']]),
            ),
            'zones' => $this->table(
                [__('ui.fields.name'), __('ui.fields.city')],
                DeliveryZone::query()->orderBy('name')->get()->map(fn (DeliveryZone $zone) => ['cells' => [$zone->name, $zone->city]]),
            ),
            'problemes' => $this->table(
                [__('ui.fields.number'), __('ui.fields.status')],
                Delivery::query()->where('status', 'failed')->with('order')->latest('id')->get()->map(fn (Delivery $delivery) => ['cells' => [$delivery->order?->number ?? '—', $delivery->status]]),
            ),
            'rapports' => $this->table(
                [__('ui.fields.metric'), __('ui.fields.value')],
                collect([
                    ['cells' => [__('ui.stats.deliveries'), (string) Delivery::query()->count()]],
                    ['cells' => [__('ui.stats.failed'), (string) Delivery::query()->where('status', 'failed')->count()]],
                ]),
            ),
            'revenus' => $this->table(
                [__('ui.fields.number'), __('ui.fields.amount')],
                Delivery::query()
                    ->when($personal, fn ($query) => $query->where('agent_id', $user->id))
                    ->where('status', 'delivered')
                    ->with('order')
                    ->latest('id')
                    ->get()
                    ->map(fn (Delivery $delivery) => ['cells' => [$delivery->order?->number ?? '—', Money::format((int) $delivery->fee_minor, $delivery->currency)]]),
            ),
            'messages' => $this->messages($user),
            default => abort(404),
        };
    }

    /**
     * @return array{columns: list<string>, rows: list<array{cells: list<string>}>}
     */
    private function customer(User $user, string $module): array
    {
        return match ($module) {
            'adresses' => $this->table(
                [__('ui.fields.label'), __('ui.fields.line')],
                Address::query()->where('user_id', $user->id)->latest('id')->get()->map(fn (Address $address) => ['cells' => [$address->label, $address->line]]),
            ),
            'favoris' => $this->table(
                [__('ui.fields.name')],
                Wishlist::query()->where('user_id', $user->id)->with('product')->latest('id')->get()->map(fn (Wishlist $row) => ['cells' => [$row->product?->name ?? '—']]),
            ),
            'boutiques' => $this->table(
                [__('ui.fields.name')],
                ShopFollow::query()->where('user_id', $user->id)->with('shop')->latest('id')->get()->map(fn (ShopFollow $row) => ['cells' => [$row->shop?->name ?? '—']]),
            ),
            'avis' => $this->table(
                [__('ui.fields.rating'), __('ui.fields.body')],
                Review::query()->where('user_id', $user->id)->latest('id')->get()->map(fn (Review $review) => ['cells' => [(string) $review->rating, (string) $review->body]]),
            ),
            'messages' => $this->messages($user),
            'support' => $this->table(
                [__('ui.fields.subject'), __('ui.fields.status')],
                SupportTicket::query()->where('user_id', $user->id)->latest('id')->get()->map(fn (SupportTicket $ticket) => ['cells' => [$ticket->subject, $ticket->status]]),
            ),
            default => abort(404),
        };
    }

    /**
     * @return array{columns: list<string>, rows: list<array{cells: list<string>}>}
     */
    private function messages(User $user): array
    {
        return $this->table(
            [__('ui.fields.body')],
            Message::query()->where('user_id', $user->id)->latest('id')->limit(50)->get()->map(fn (Message $message) => ['cells' => [$message->body]]),
        );
    }

    /**
     * @param  list<string>  $columns
     * @param  Collection<int, array{cells: list<string>, suspend_user_id?: int}>|list<array{cells: list<string>}>  $rows
     * @return array{columns: list<string>, rows: list<array{cells: list<string>, suspend_user_id?: int}>}
     */
    private function table(array $columns, Collection|array $rows): array
    {
        $list = $rows instanceof Collection ? $rows->all() : $rows;

        return [
            'columns' => $columns,
            'rows' => array_values($list),
        ];
    }
}
