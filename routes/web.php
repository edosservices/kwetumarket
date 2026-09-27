<?php

use App\Enums\AccountArea;
use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\AccountRedirectController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserSuspensionController;
use App\Http\Controllers\Admin\VendorModerationController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ModuleController as CustomerModuleController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Delivery\DashboardController as DeliveryDashboardController;
use App\Http\Controllers\Delivery\MissionController;
use App\Http\Controllers\Delivery\ModuleController as DeliveryModuleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ModuleController as VendorModuleController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Services\Rbac\ModuleDirectory;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/recherche', SearchController::class)->name('search');
Route::get('/categories', [CatalogController::class, 'categories'])->name('categories.index');
Route::get('/boutiques', [CatalogController::class, 'shops'])->name('shops.index');
Route::get('/panier', [CatalogController::class, 'cart'])->name('cart.show');
Route::get('/langue/{locale}', LocaleController::class)->name('locale.switch');

Route::get('/favicon.ico', function () {
    return response()->file(public_path(config('twende.logo')), [
        'Content-Type' => 'image/png',
    ]);
})->name('favicon');

Route::get('/manifest.webmanifest', function () {
    $manifest = [
        'name' => config('twende.name'),
        'short_name' => 'Twende',
        'description' => __('ui.seo.default_description'),
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#FFFFFF',
        'theme_color' => config('twende.colors.red'),
        'lang' => app()->getLocale(),
        'icons' => [[
            'src' => asset(config('twende.logo')),
            'sizes' => '1774x887',
            'type' => 'image/png',
            'purpose' => 'any',
        ]],
    ];

    return response()->json($manifest, 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ->header('Content-Type', 'application/manifest+json');
})->name('manifest');

Route::get('/sitemap.xml', function () {
    $urls = [
        route('home'),
        route('search'),
        route('categories.index'),
        route('shops.index'),
        route('login'),
        route('register'),
    ];

    return response()
        ->view('sitemap', ['urls' => $urls])
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $body = "User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n";

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tableau-de-bord', AccountRedirectController::class)->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/notifications', NotificationController::class)->name('notifications.index');

    $modules = fn (AccountArea $area): string => implode('|', app(ModuleDirectory::class)->keys($area));

    Route::middleware('role:'.AccountArea::Admin->middleware())->prefix('admin')->group(function () use ($modules) {
        Route::get('/', AdminDashboardController::class)->name('admin.dashboard');
        Route::post('/utilisateurs/{user}/suspendre', [UserSuspensionController::class, 'store'])
            ->middleware('permission:users.suspend')
            ->name('admin.users.suspend');
        Route::post('/utilisateurs/{user}/reactiver', [UserSuspensionController::class, 'destroy'])
            ->middleware('permission:users.suspend')
            ->name('admin.users.reactivate');
        Route::post('/parametres', [SettingController::class, 'update'])
            ->middleware('permission:settings.edit')
            ->name('admin.settings.update');
        Route::get('/produits/{product}/modifier', [AdminProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/produits/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::post('/produits/{product}/approuver', [AdminProductController::class, 'approve'])
            ->middleware('permission:products.approve')
            ->name('admin.products.approve');
        Route::get('/commandes/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::put('/commandes/{order}', [AdminOrderController::class, 'update'])->name('admin.orders.update');
        Route::post('/vendeurs/{vendor}/approuver', [VendorModerationController::class, 'approve'])
            ->middleware('permission:vendors.approve')
            ->name('admin.vendors.approve');
        Route::post('/vendeurs/{vendor}/suspendre', [VendorModerationController::class, 'suspend'])
            ->middleware('permission:vendors.suspend')
            ->name('admin.vendors.suspend');
        Route::post('/remboursements', [AdminFinanceController::class, 'refund'])
            ->middleware('permission:finance.refunds')
            ->name('admin.refunds.store');
        Route::post('/retraits/{payout}/approuver', [AdminFinanceController::class, 'approvePayout'])
            ->middleware('permission:finance.payouts')
            ->name('admin.payouts.approve');
        Route::get('/{module}', AdminModuleController::class)
            ->where('module', $modules(AccountArea::Admin))
            ->name('admin.module');
    });

    Route::middleware('role:'.AccountArea::Vendor->middleware())->prefix('vendeur')->group(function () use ($modules) {
        Route::get('/', VendorDashboardController::class)->name('vendor.dashboard');
        Route::get('/produits', [ProductController::class, 'index'])->name('vendor.products.index');
        Route::post('/produits', [ProductController::class, 'store'])->name('vendor.products.store');
        Route::get('/produits/{product}', [ProductController::class, 'show'])->name('vendor.products.show');
        Route::put('/produits/{product}', [ProductController::class, 'update'])->name('vendor.products.update');
        Route::get('/commandes/{order}', [VendorOrderController::class, 'show'])->name('vendor.orders.show');
        Route::put('/commandes/{order}', [VendorOrderController::class, 'update'])->name('vendor.orders.update');
        Route::get('/{module}', VendorModuleController::class)
            ->where('module', $modules(AccountArea::Vendor))
            ->name('vendor.module');
    });

    Route::middleware('role:'.AccountArea::Delivery->middleware())->prefix('livreur')->group(function () use ($modules) {
        Route::get('/', DeliveryDashboardController::class)->name('delivery.dashboard');
        Route::get('/missions', [MissionController::class, 'index'])->name('delivery.missions');
        Route::get('/missions/{delivery}', [MissionController::class, 'show'])->name('delivery.missions.show');
        Route::put('/missions/{delivery}', [MissionController::class, 'update'])->name('delivery.missions.update');
        Route::post('/missions/{delivery}/affecter', [MissionController::class, 'assign'])->name('delivery.missions.assign');
        Route::get('/{module}', DeliveryModuleController::class)
            ->where('module', $modules(AccountArea::Delivery))
            ->name('delivery.module');
    });

    Route::middleware('role:'.AccountArea::Customer->middleware())->prefix('compte')->group(function () use ($modules) {
        Route::get('/', CustomerDashboardController::class)->name('customer.dashboard');
        Route::get('/commandes', [CustomerOrderController::class, 'index'])->name('customer.orders');
        Route::get('/commandes/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
        Route::post('/avis', [CustomerReviewController::class, 'store'])->name('customer.reviews.store');
        Route::get('/{module}', CustomerModuleController::class)
            ->where('module', $modules(AccountArea::Customer))
            ->name('customer.module');
    });
});
