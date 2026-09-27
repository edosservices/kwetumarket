<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_official_logo_file_is_the_one_shipped_by_the_owner(): void
    {
        $path = public_path(config('twende.logo'));

        $this->assertFileExists($path);
        $this->assertGreaterThan(100_000, filesize($path));
        $this->assertSame('brand/twende-market-logo.png', config('twende.logo'));
    }

    public function test_brand_colors_are_centralized_in_tailwind(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        foreach (config('twende.colors') as $name => $hex) {
            $this->assertStringContainsString($hex, $css, $name);
        }
    }

    public function test_brand_logo_component_uses_the_configured_file(): void
    {
        $html = Blade::render('<x-brand-logo />');

        $this->assertStringContainsString(config('twende.logo'), $html);
        $this->assertStringContainsString('object-contain', $html);
        $this->assertStringContainsString('Twende Market', $html);

        config(['twende.logo' => 'brand/autre-logo.png']);

        $html = Blade::render('<x-brand-logo />');

        $this->assertStringContainsString('brand/autre-logo.png', $html);
        $this->assertStringNotContainsString('twende-market-logo.png', $html);
    }

    public function test_homepage_header_search_and_sections_use_the_logo(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('brand/twende-market-logo.png', false);
        $response->assertSee('object-contain', false);
        $response->assertSee(__('ui.home.title'));
        $response->assertSee(__('ui.home.categories_title'));
        $response->assertSee(__('ui.home.popular'));
        $response->assertSee(__('ui.home.promotions'));
        $response->assertSee(__('ui.home.bestsellers'));
        $response->assertSee(__('ui.home.shops'));
        $response->assertSee(__('ui.home.newest'));
        $response->assertSee('hidden items-center gap-1 sm:flex', false);
        $this->assertDoesNotMatchRegularExpression(
            '/class="[^"]*\binline-flex\b[^"]*\bhidden sm:inline-flex\b/',
            $response->getContent(),
        );
    }

    public function test_manifest_favicon_and_mail_use_the_official_logo(): void
    {
        $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json')
            ->assertSee('twende-market-logo.png', false);

        $this->get('/favicon.ico')
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $header = file_get_contents(resource_path('views/vendor/mail/html/header.blade.php'));

        $this->assertStringContainsString("config('twende.logo')", $header);
        $this->assertStringContainsString('object-fit: contain', $header);
        $this->assertStringNotContainsString('laravel.com/img', $header);
    }

    public function test_design_system_components_render(): void
    {
        $product = Blade::render('<x-product-card name="Riz" price="2500" currency="CDF" shop="Kinshasa" badge="Promo" />');
        $this->assertStringContainsString('Riz', $product);
        $this->assertStringContainsString('2500', $product);
        $this->assertStringContainsString('CDF', $product);

        $shop = Blade::render('<x-shop-card name="Marché Central" location="Gombe" />');
        $this->assertStringContainsString('Marché Central', $shop);

        $category = Blade::render('<x-category-card name="Alimentation" />');
        $this->assertStringContainsString('Alimentation', $category);

        $empty = Blade::render('<x-empty-state title="Vide" description="Rien ici." />');
        $this->assertStringContainsString('Vide', $empty);

        $alert = Blade::render('<x-alert variant="success">Enregistré</x-alert>');
        $this->assertStringContainsString('Enregistré', $alert);
    }
}
