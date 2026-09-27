<?php

namespace App\Providers;

use App\Actions\Auth\AssignClientRole;
use App\Actions\Auth\MergeGuestCart;
use App\Actions\Auth\SyncUserLocale;
use App\Contracts\CatalogImporter;
use App\Contracts\ImageRecognitionInterface;
use App\Contracts\ProductSearch;
use App\Contracts\SmsGateway;
use App\Services\Catalog\CsvCatalogImporter;
use App\Services\Commerce\CartService;
use App\Services\Search\EloquentProductSearch;
use App\Services\Search\NullProductSearch;
use App\Services\Sms\LogSmsGateway;
use App\Services\Vision\ImageRecognitionManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, function () {
            return match (config('twende.sms.driver')) {
                'log' => new LogSmsGateway,
                default => throw new RuntimeException('SMS driver ['.config('twende.sms.driver').'] is not configured.'),
            };
        });

        $this->app->singleton(ImageRecognitionInterface::class, ImageRecognitionManager::class);
        $this->app->bind(CatalogImporter::class, CsvCatalogImporter::class);

        $this->app->bind(ProductSearch::class, function () {
            return match (config('twende.search.driver')) {
                'null' => new NullProductSearch,
                'database' => new EloquentProductSearch,
                default => throw new RuntimeException('Search driver ['.config('twende.search.driver').'] is not implemented yet.'),
            };
        });
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->mixedCase()->numbers());

        Event::listen(Registered::class, AssignClientRole::class);
        Event::listen(Login::class, SyncUserLocale::class);
        Event::listen(Login::class, MergeGuestCart::class);

        View::composer(['components.site-header', 'components.layouts.dashboard', 'components.mobile-nav'], function ($view): void {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('unreadNotifications', auth()->user()?->unreadNotifications()->count() ?? 0);
        });

        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('image-search', function (Request $request) {
            return Limit::perMinute((int) config('twende.vision.rate_per_minute'))->by($request->ip());
        });
    }
}
