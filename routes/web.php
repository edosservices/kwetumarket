<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ShopController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\ProductImageController;
use App\Http\Controllers\Vendor\ProductVariantController;
use App\Http\Controllers\Vendor\ShopController as VendorShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/recherche', SearchController::class)->name('search');
Route::get('/produits', [ProductController::class, 'index'])->name('products.index');
Route::get('/produit/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorie/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/boutiques', [ShopController::class, 'index'])->name('shops.index');
Route::get('/boutique/{shop}', [ShopController::class, 'show'])->name('shops.show');
Route::get('/panier', [CatalogController::class, 'cart'])->name('cart.show');
Route::redirect('/vendor', '/vendeur');
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
        route('products.index'),
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
    Route::get('/tableau-de-bord', [DashboardController::class, 'home'])->name('dashboard');
    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile.edit');
    Route::get('/vendeur', [DashboardController::class, 'vendor'])->middleware('role:vendor|admin')->name('vendor.dashboard');
    Route::get('/livreur', [DashboardController::class, 'delivery'])->middleware('role:delivery_agent|admin')->name('delivery.dashboard');
    Route::get('/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('admin.dashboard');

    Route::middleware('role:vendor|admin')->prefix('vendeur')->name('vendor.')->group(function () {
        Route::get('/boutique', [VendorShopController::class, 'edit'])->name('shop.edit');
        Route::put('/boutique', [VendorShopController::class, 'update'])->name('shop.update');
        Route::get('/produits', [VendorProductController::class, 'index'])->name('products.index');
        Route::get('/produits/nouveau', [VendorProductController::class, 'create'])->name('products.create');
        Route::post('/produits', [VendorProductController::class, 'store'])->name('products.store');
        Route::get('/produits/{product}/modifier', [VendorProductController::class, 'edit'])->name('products.edit');
        Route::put('/produits/{product}', [VendorProductController::class, 'update'])->name('products.update');
        Route::delete('/produits/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/produits/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::put('/produits/{product}/images/{image}/principale', [ProductImageController::class, 'primary'])->name('products.images.primary');
        Route::delete('/produits/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::post('/produits/{product}/variantes', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/produits/{product}/variantes/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/produits/{product}/variantes/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::get('/stock', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/stock', [InventoryController::class, 'store'])->name('inventory.store');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/nouveau', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/modifier', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/brands', [AdminBrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/nouveau', [AdminBrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [AdminBrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/modifier', [AdminBrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [AdminBrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

        Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
        Route::post('/vendors', [AdminVendorController::class, 'store'])->name('vendors.store');
        Route::put('/vendors/{vendor}', [AdminVendorController::class, 'update'])->name('vendors.update');

        Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
        Route::get('/shops/{shop}/modifier', [AdminShopController::class, 'edit'])->name('shops.edit');
        Route::put('/shops/{shop}', [AdminShopController::class, 'update'])->name('shops.update');

        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}/modifier', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [AdminInventoryController::class, 'store'])->name('inventory.store');
    });
});
