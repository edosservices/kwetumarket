<?php

use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ShopController;
use App\Http\Controllers\Api\V1\ShopLocationController;
use App\Http\Controllers\Api\V1\SmartSearchController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\Vendor\ProductController as VendorProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    Route::get('/categories', [CategoryController::class, 'index'])->name('api.v1.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.v1.categories.show');
    Route::get('/products/nearby', [SmartSearchController::class, 'products'])->name('api.v1.products.nearby');
    Route::get('/products', [ProductController::class, 'index'])->name('api.v1.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.v1.products.show');
    Route::get('/shops/nearby', [SmartSearchController::class, 'shops'])->name('api.v1.shops.nearby');
    Route::get('/shops', [ShopController::class, 'index'])->name('api.v1.shops.index');
    Route::get('/shops/{shop}', [ShopController::class, 'show'])->name('api.v1.shops.show');

    Route::post('/search/image', [SmartSearchController::class, 'image'])
        ->middleware('throttle:image-search')
        ->name('api.v1.search.image');
    Route::get('/search/nearby', [SmartSearchController::class, 'nearby'])->name('api.v1.search.nearby');
    Route::get('/promotions/nearby', [SmartSearchController::class, 'promotions'])->name('api.v1.promotions.nearby');

    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:api-login')
        ->name('api.v1.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/user', [UserController::class, 'show'])->name('api.v1.user');
        Route::post('/shops/{shop}/location', [ShopLocationController::class, 'update'])->name('api.v1.shops.location');

        Route::middleware('role:vendor|admin,sanctum')->prefix('vendor')->group(function () {
            Route::get('/products', [VendorProductController::class, 'index'])->name('api.v1.vendor.products.index');
            Route::post('/products', [VendorProductController::class, 'store'])->name('api.v1.vendor.products.store');
            Route::get('/products/{product}', [VendorProductController::class, 'show'])->name('api.v1.vendor.products.show');
            Route::put('/products/{product}', [VendorProductController::class, 'update'])->name('api.v1.vendor.products.update');
        });

        Route::middleware('role:admin,sanctum')->prefix('admin')->group(function () {
            Route::get('/products', [AdminProductController::class, 'index'])->name('api.v1.admin.products.index');
        });
    });
});
