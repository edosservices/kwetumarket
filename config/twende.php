<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identité Twende Market
    |--------------------------------------------------------------------------
    |
    | Le fichier logo est l'asset officiel fourni par le propriétaire.
    | Il ne doit pas être redessiné, recoloré ou remplacé.
    |
    */

    'name' => 'Twende Market',

    'logo' => 'brand/twende-market-logo.png',

    /*
    |--------------------------------------------------------------------------
    | Couleurs échantillonnées sur le logo officiel
    |--------------------------------------------------------------------------
    |
    | Rouge #F20205 : aplats du mot « twende ».
    | Vert #0D9827 : mot « market » et feuilles.
    | Vert vif #03A93A : élément du chariot.
    | Les teintes hover sont des assombrissements de ces couleurs.
    | Les surfaces sombres sont neutres afin de poser le logo, inchangé,
    | sur un fond blanc suffisamment contrasté.
    |
    | Ces valeurs sont dupliquées dans resources/css/app.css pour Tailwind.
    |
    */

    'colors' => [
        'red' => '#F20205',
        'red_dark' => '#B80104',
        'green' => '#0D9827',
        'green_dark' => '#08701C',
        'green_bright' => '#03A93A',
        'dark' => '#1A1A1A',
        'muted' => '#5E675F',
        'light' => '#F4F7F5',
        'line' => '#E3EAE4',
        'night' => '#121614',
        'night_card' => '#1C2620',
    ],

    'locales' => ['fr', 'en', 'ln', 'sw'],

    'currencies' => ['CDF', 'USD'],

    'currency' => [
        'default' => env('TWENDE_CURRENCY', 'CDF'),
    ],

    'otp' => [
        'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 10),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'length' => 6,
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Recherche
    |--------------------------------------------------------------------------
    |
    | « database » interroge MySQL/Eloquent. « null » renvoie un catalogue vide.
    | Le contrat App\Contracts\ProductSearch permettra de brancher Meilisearch
    | plus tard sans changer les contrôleurs.
    |
    */

    'search' => [
        'driver' => env('SEARCH_DRIVER', 'database') ?: 'database',
    ],

    'media' => [
        'disk' => env('MEDIA_DISK', 'public'),
        'max_kilobytes' => 5120,
    ],

    'developer' => 'Édouard Bengehya',

    'contact_email' => env('TWENDE_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'bonjour@twende.market')),

    /*
    |--------------------------------------------------------------------------
    | Recherche intelligente
    |--------------------------------------------------------------------------
    |
    | « local » analyse uniquement la couleur dominante et le dit clairement.
    | « openai » n'est utilisé que si OPENAI_API_KEY est renseignée.
    | Google Vision et AWS Rekognition sont prévus, sans résultat inventé.
    |
    */

    'vision' => [
        'driver' => env('VISION_DRIVER', 'local') ?: 'local',
        'directory' => 'search-images',
        'retention_hours' => (int) env('SEARCH_IMAGE_RETENTION_HOURS', 24),
        'max_kilobytes' => (int) env('SEARCH_IMAGE_MAX_KB', 4096),
        'min_edge' => 32,
        'max_edge' => 8000,
        'rate_per_minute' => 8,
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_VISION_MODEL', 'gpt-4o-mini'),
        ],
    ],

    'nearby' => [
        'default_radius_km' => 2,
        'max_radius_km' => 10,
        'radii_km' => [0.5, 1, 2, 5, 10],
        'low_stock' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commerce
    |--------------------------------------------------------------------------
    |
    | Les montants restent en unité minimale. La commission est retenue sur
    | le vendeur et n'est pas ajoutée au total client. tax_percent à 0
    | n'invente pas de taxe. PAYMENT_DRIVER=sandbox n'effectue aucun débit réel.
    |
    */

    'commerce' => [
        'tax_percent' => (int) env('TWENDE_TAX_PERCENT', 0),
        'commission_percent' => (int) env('TWENDE_COMMISSION_PERCENT', 10),
        'payment_driver' => env('PAYMENT_DRIVER', 'sandbox') ?: 'sandbox',
        'referral_reward' => (int) env('TWENDE_REFERRAL_REWARD', 500000),
        'methods' => ['cod', 'sandbox'],
    ],

];
