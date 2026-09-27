<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommerceController as AdminCommerceController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ShopController;
use App\Http\Controllers\Commerce\AccountController;
use App\Http\Controllers\Commerce\AddressController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\CheckoutController;
use App\Http\Controllers\Commerce\OrderController;
use App\Http\Controllers\Commerce\ReturnController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Delivery\JobController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageSearchController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NearbyController;
use App\Http\Controllers\Payments\WebhookController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Vendor\CatalogImportController;
use App\Http\Controllers\Vendor\CommerceController as VendorCommerceController;
use App\Http\Controllers\Vendor\DropshipController;
use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\ProductImageController;
use App\Http\Controllers\Vendor\ProductVariantController;
use App\Http\Controllers\Vendor\ShopController as VendorShopController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/payments/{provider}', WebhookController::class)->name('payments.webhook');
Route::get('/hors-ligne', fn () => view('pages.offline'))->name('offline');

Route::get('/', HomeController::class)->name('home');
Route::get('/recherche', SearchController::class)->name('search');
Route::post('/recherche/image', [ImageSearchController::class, 'store'])->middleware('throttle:image-search')->name('search.image.store');
Route::get('/recherche/image/{imageSearch}', [ImageSearchController::class, 'show'])->name('search.image.show');
Route::get('/recherche/image/{imageSearch}/fichier', [ImageSearchController::class, 'file'])->name('search.image.file');
Route::get('/pres-de-moi', NearbyController::class)->name('nearby');
Route::get('/promotions', PromotionController::class)->name('promotions');
Route::get('/a-propos', fn () => app(CompanyController::class)->show('about'))->name('about');
Route::get('/contact', fn () => app(CompanyController::class)->show('contact'))->name('contact');
Route::get('/conditions', fn () => app(CompanyController::class)->show('terms'))->name('terms');
Route::get('/confidentialite', fn () => app(CompanyController::class)->show('privacy'))->name('privacy');
Route::get('/vendre', fn () => app(CompanyController::class)->show('sell'))->name('sell');
Route::get('/aide', fn () => app(CompanyController::class)->show('help'))->name('help');
Route::get('/faq', fn () => app(CompanyController::class)->show('faq'))->name('faq');
Route::get('/produits', [ProductController::class, 'index'])->name('products.index');
Route::get('/produit/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorie/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/boutiques', [ShopController::class, 'index'])->name('shops.index');
Route::get('/boutique/{shop}', [ShopController::class, 'show'])->name('shops.show');
Route::get('/panier', [CartController::class, 'show'])->name('cart.show');
Route::post('/panier', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/panier/articles/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/panier/articles/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/panier', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/panier/coupon', [CartController::class, 'coupon'])->name('cart.coupon');
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
        route('nearby'),
        route('promotions'),
        route('about'),
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
    Route::get('/commande', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/commande', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/commandes', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/commandes/{order}/recu', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::post('/commandes/{order}/annuler', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/commandes/{order}/avis', [OrderController::class, 'review'])->name('orders.review');
    Route::post('/commandes/{order}/litige', [OrderController::class, 'dispute'])->name('orders.dispute');
    Route::post('/commandes/{order}/remboursement', [OrderController::class, 'refund'])->name('orders.refund');
    Route::post('/commandes/{order}/reessayer', [OrderController::class, 'retry'])->name('orders.retry');
    Route::post('/commandes/{order}/retour', [ReturnController::class, 'store'])->name('orders.return');
    Route::get('/adresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/adresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/adresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/adresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/adresses/{address}/principale', [AddressController::class, 'primary'])->name('addresses.primary');
    Route::get('/points', [AccountController::class, 'points'])->name('points.index');
    Route::get('/coupons', [AccountController::class, 'coupons'])->name('coupons.index');
    Route::get('/avis', [AccountController::class, 'reviews'])->name('reviews.index');
    Route::get('/favoris', [AccountController::class, 'favorites'])->name('favorites.index');
    Route::post('/favoris/{product}', [AccountController::class, 'favorite'])->name('favorites.store');
    Route::delete('/favoris/{product}', [AccountController::class, 'unfavorite'])->name('favorites.destroy');
    Route::get('/boutiques-suivies', [AccountController::class, 'follows'])->name('follows.index');
    Route::post('/boutiques/{shop}/suivre', [AccountController::class, 'follow'])->name('follows.store');
    Route::delete('/boutiques/{shop}/suivre', [AccountController::class, 'unfollow'])->name('follows.destroy');
    Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/lues', [AccountController::class, 'readNotifications'])->name('notifications.read');
    Route::get('/messages', [AccountController::class, 'messages'])->name('messages.index');
    Route::get('/messages/{conversation}', [AccountController::class, 'showMessage'])->name('messages.show');
    Route::post('/messages', [AccountController::class, 'sendMessage'])->name('messages.store');
    Route::post('/messages/{conversation}', [AccountController::class, 'reply'])->name('messages.reply');
    Route::get('/parrainage', [AccountController::class, 'referral'])->name('referral');

    Route::get('/tableau-de-bord', [DashboardController::class, 'home'])->name('dashboard');
    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile.edit');
    Route::get('/profil/photo', [ProfilePhotoController::class, 'show'])->name('profile.photo');
    Route::post('/profil/photo', [ProfilePhotoController::class, 'update'])->name('profile.photo.update');
    Route::get('/vendeur', [DashboardController::class, 'vendor'])->middleware('role:vendor|admin')->name('vendor.dashboard');
    Route::get('/livreur', [DashboardController::class, 'delivery'])->middleware('role:delivery_agent|admin')->name('delivery.dashboard');
    Route::get('/livreur/missions', [JobController::class, 'index'])->middleware('role:delivery_agent|admin')->name('delivery.jobs');
    Route::post('/livreur/missions/{delivery}', [JobController::class, 'advance'])->middleware('role:delivery_agent|admin')->name('delivery.jobs.advance');
    Route::post('/livreur/profil', [JobController::class, 'profile'])->middleware('role:delivery_agent|admin')->name('delivery.profile');
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
        Route::post('/produits/{product}/dupliquer', [VendorProductController::class, 'duplicate'])->name('products.duplicate');
        Route::post('/produits/{product}/statut', [VendorProductController::class, 'status'])->name('products.status');
        Route::post('/produits/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::put('/produits/{product}/images/{image}/principale', [ProductImageController::class, 'primary'])->name('products.images.primary');
        Route::post('/produits/{product}/images/{image}/ordre', [ProductImageController::class, 'move'])->name('products.images.move');
        Route::get('/dropshipping', [DropshipController::class, 'index'])->name('dropship.index');
        Route::post('/dropshipping/offres', [DropshipController::class, 'offer'])->name('dropship.offer');
        Route::put('/dropshipping/offres/{offer}', [DropshipController::class, 'sync'])->name('dropship.sync');
        Route::post('/dropshipping/offres/{offer}/importer', [DropshipController::class, 'import'])->name('dropship.import');
        Route::put('/dropshipping/{product}', [DropshipController::class, 'update'])->name('dropship.update');
        Route::get('/import', [CatalogImportController::class, 'create'])->name('import');
        Route::post('/import', [CatalogImportController::class, 'store'])->name('import.store');
        Route::delete('/produits/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::post('/produits/{product}/variantes', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/produits/{product}/variantes/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/produits/{product}/variantes/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::get('/stock', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/stock', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/commandes', [VendorCommerceController::class, 'orders'])->name('orders.index');
        Route::get('/clients', [VendorCommerceController::class, 'customers'])->name('customers');
        Route::post('/commandes/{order}/preparer', [VendorCommerceController::class, 'prepare'])->name('orders.prepare');
        Route::post('/commandes/{order}/prete', [VendorCommerceController::class, 'ready'])->name('orders.ready');
        Route::post('/retours/{returnRequest}', [ReturnController::class, 'vendorDecide'])->name('returns.decide');
        Route::get('/avis', [VendorCommerceController::class, 'reviews'])->name('reviews.index');
        Route::post('/avis/{review}', [VendorCommerceController::class, 'replyReview'])->name('reviews.reply');
        Route::get('/portefeuille', [VendorCommerceController::class, 'wallet'])->name('wallet');
        Route::post('/retraits', [VendorCommerceController::class, 'withdraw'])->name('withdrawals.store');
        Route::get('/abonnement', [VendorCommerceController::class, 'subscription'])->name('subscription');
        Route::post('/abonnement', [VendorCommerceController::class, 'subscribe'])->name('subscription.store');
        Route::get('/certification', [VendorCommerceController::class, 'certification'])->name('certification');
        Route::post('/certification', [VendorCommerceController::class, 'requestCertification'])->name('certification.store');
        Route::get('/publicites', [VendorCommerceController::class, 'ads'])->name('ads');
        Route::post('/publicites', [VendorCommerceController::class, 'storeAd'])->name('ads.store');
        Route::get('/promotions', [VendorCommerceController::class, 'promotions'])->name('promotions');
        Route::post('/promotions', [VendorCommerceController::class, 'storePromotion'])->name('promotions.store');
        Route::get('/statistiques', [VendorCommerceController::class, 'analytics'])->name('analytics');
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
        Route::get('/users', [AdminCommerceController::class, 'users'])->name('users.index');
        Route::get('/orders', [AdminCommerceController::class, 'orders'])->name('orders.index');
        Route::get('/payments', [AdminCommerceController::class, 'payments'])->name('payments.index');
        Route::get('/reviews', [AdminCommerceController::class, 'reviews'])->name('reviews.index');
        Route::get('/deliveries', [AdminCommerceController::class, 'deliveries'])->name('deliveries.index');
        Route::post('/deliveries/{delivery}/assign', [AdminCommerceController::class, 'assign'])->name('deliveries.assign');
        Route::get('/withdrawals', [AdminCommerceController::class, 'withdrawals'])->name('withdrawals.index');
        Route::post('/withdrawals/{withdrawal}', [AdminCommerceController::class, 'decideWithdrawal'])->name('withdrawals.update');
        Route::get('/disputes', [AdminCommerceController::class, 'disputes'])->name('disputes.index');
        Route::post('/disputes/{dispute}', [AdminCommerceController::class, 'resolveDispute'])->name('disputes.update');
        Route::get('/refunds', [AdminCommerceController::class, 'refunds'])->name('refunds.index');
        Route::post('/refunds/{refund}', [AdminCommerceController::class, 'decideRefund'])->name('refunds.update');
        Route::get('/returns', [ReturnController::class, 'adminIndex'])->name('returns.index');
        Route::post('/returns/{returnRequest}', [ReturnController::class, 'adminDecide'])->name('returns.update');
        Route::get('/ads', [AdminCommerceController::class, 'ads'])->name('ads.index');
        Route::post('/ads/{ad}', [AdminCommerceController::class, 'decideAd'])->name('ads.update');
        Route::get('/certifications', [AdminCommerceController::class, 'certifications'])->name('certifications.index');
        Route::post('/certifications/{certification}', [AdminCommerceController::class, 'decideCertification'])->name('certifications.update');
        Route::get('/analytics', [AdminCommerceController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [AdminCommerceController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminCommerceController::class, 'updateSettings'])->name('settings.update');
        Route::get('/hero', [HeroSlideController::class, 'index'])->name('hero.index');
        Route::post('/hero', [HeroSlideController::class, 'store'])->name('hero.store');
        Route::put('/hero/{slide}', [HeroSlideController::class, 'update'])->name('hero.update');
        Route::delete('/hero/{slide}', [HeroSlideController::class, 'destroy'])->name('hero.destroy');
    });
});
