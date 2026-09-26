<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SearchController;
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
    Route::get('/tableau-de-bord', [DashboardController::class, 'home'])->name('dashboard');
    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile.edit');
    Route::get('/vendeur', [DashboardController::class, 'vendor'])->middleware('role:vendor|admin')->name('vendor.dashboard');
    Route::get('/livreur', [DashboardController::class, 'delivery'])->middleware('role:delivery_agent|admin')->name('delivery.dashboard');
    Route::get('/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('admin.dashboard');
});
