<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use App\Models\HeroSlide;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Services\Commerce\ExchangeRateService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $rate = ExchangeRate::query()->firstOrCreate(
            ['base' => 'USD', 'quote' => 'CDF', 'quoted_at' => now()->startOfDay()],
            ['minor_per_unit' => 280000],
        );

        foreach ([
            'points_per_referral' => (string) config('twende.points.per_referral'),
            'points_per_usd' => (string) config('twende.points.per_usd'),
            'points_min_conversion' => (string) config('twende.points.min_conversion'),
            'points_max_daily' => (string) config('twende.points.max_daily'),
            'free_shipping_minor' => (string) config('twende.commerce.free_shipping_minor'),
        ] as $key => $value) {
            PlatformSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $cdf = app(ExchangeRateService::class)->usdCentsToQuoteMinor(300);

        if ($cdf !== null) {
            SubscriptionPlan::query()->where('slug', 'boutique')->update(['price' => $cdf, 'price_usd_cents' => 300]);
        }

        if (HeroSlide::query()->exists() || ! function_exists('imagecreatetruecolor')) {
            return;
        }

        $disk = (string) config('twende.media.disk');
        $slides = [
            ['home', 'Grandes promotions Twende', 'Des offres choisies près de chez vous', 'Découvrir', '/promotions'],
            ['home', 'Boutiques de votre quartier', 'Livraison et retrait selon la zone', 'Voir les boutiques', '/boutiques'],
            ['auth', 'Rejoignez Twende Market', 'Achetez, vendez et faites-vous livrer', 'Créer un compte', '/register'],
        ];

        foreach ($slides as $index => [$placement, $title, $subtitle, $cta, $url]) {
            $image = imagecreatetruecolor(1200, 640);
            $red = imagecolorallocate($image, 242, 2, 5);
            $green = imagecolorallocate($image, 13, 152, 39);
            imagefilledrectangle($image, 0, 0, 1200, 640, $index % 2 === 0 ? $red : $green);
            $path = 'marketing/hero/slide-'.$index.'.jpg';
            $absolute = Storage::disk($disk)->path($path);
            if (! is_dir(dirname($absolute))) {
                mkdir(dirname($absolute), 0755, true);
            }
            imagejpeg($image, $absolute, 85);
            imagedestroy($image);

            HeroSlide::query()->create([
                'placement' => $placement,
                'title' => $title,
                'subtitle' => $subtitle,
                'cta_label' => $cta,
                'cta_url' => $url,
                'image' => $path,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
