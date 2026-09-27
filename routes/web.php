<?php

use App\Enums\AccountArea;
use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\AccountRedirectController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserSuspensionController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ModuleController as CustomerModuleController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Delivery\DashboardController as DeliveryDashboardController;
use App\Http\Controllers\Delivery\MissionController;
use App\Http\Controllers\Delivery\ModuleController as DeliveryModuleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ModuleController as VendorModuleController;
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
        Route::post('/parametres', [SettingController::class, 'update'])
            ->middleware('permission:settings.edit')
            ->name('admin.settings.update');
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
        Route::get('/{module}', CustomerModuleController::class)
            ->where('module', $modules(AccountArea::Customer))
            ->name('customer.module');
    });
});
