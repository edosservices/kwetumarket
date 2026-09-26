<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('twende.locales', ['fr']);
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        if (! is_string($locale) || ! in_array($locale, $available, true)) {
            $userLocale = $request->user()?->locale;
            $locale = is_string($userLocale) && in_array($userLocale, $available, true)
                ? $userLocale
                : config('app.locale');
        }

        if (is_string($locale) && in_array($locale, $available, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
