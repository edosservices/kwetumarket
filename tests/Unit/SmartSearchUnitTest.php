<?php

namespace Tests\Unit;

use App\Enums\SocialPlatform;
use App\Models\Shop;
use App\Models\Vendor;
use App\Models\VendorSocialLink;
use App\Services\Catalog\OfferPricing;
use App\Services\Catalog\VendorContacts;
use App\Services\Vision\LocalImageAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartSearchUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_analyzer_reports_a_color_without_claiming_a_product(): void
    {
        $insight = (new LocalImageAnalyzer)->analyze($this->bluePng());

        $this->assertTrue($insight->limited);
        $this->assertSame('local', $insight->provider);
        $this->assertSame('bleu', $insight->color);
        $this->assertNull($insight->brand);
        $this->assertNull($insight->category);
        $this->assertLessThan(0.85, $insight->confidence);
    }

    public function test_contact_links_are_built_only_from_provided_channels(): void
    {
        $vendor = Vendor::factory()->create();
        $shop = Shop::factory()->create([
            'vendor_id' => $vendor->id,
            'phone' => '+243810000333',
            'email' => 'boutique@twende.market',
        ]);
        VendorSocialLink::query()->create([
            'vendor_id' => $vendor->id,
            'platform' => SocialPlatform::Whatsapp,
            'username' => '+243810000333',
        ]);
        VendorSocialLink::query()->create([
            'vendor_id' => $vendor->id,
            'platform' => SocialPlatform::Instagram,
            'username' => 'modeplus',
        ]);

        $links = collect(app(VendorContacts::class)->forShop($shop, 'Robe bleue'))->keyBy('platform');

        $this->assertSame('tel:+243810000333', $links['phone']->href);
        $this->assertStringStartsWith('https://wa.me/243810000333?text=', $links['whatsapp']->href);
        $this->assertStringContainsString(rawurlencode('Robe bleue'), $links['whatsapp']->href);
        $this->assertSame('https://instagram.com/modeplus', $links['instagram']->href);
        $this->assertArrayNotHasKey('tiktok', $links->all());
        $this->assertArrayNotHasKey('facebook', $links->all());
        $this->assertSame('mailto:boutique@twende.market', $links['email']->href);
    }

    public function test_active_promotion_changes_the_server_price_and_an_expired_one_does_not(): void
    {
        $active = \App\Models\Product::factory()->published()->create(['price' => 5000, 'compare_at_price' => null]);
        $expired = \App\Models\Product::factory()->published()->create(['price' => 8000, 'compare_at_price' => null]);
        $active->promotions()->create([
            'promotional_price' => 4250,
            'is_active' => true,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ]);
        $expired->promotions()->create([
            'promotional_price' => 1000,
            'is_active' => true,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subMinute(),
        ]);

        $active->load('activePromotion');
        $expired->load('activePromotion');
        $activePrice = OfferPricing::forProduct($active);
        $expiredPrice = OfferPricing::forProduct($expired);

        $this->assertSame(4250, $activePrice->finalPrice);
        $this->assertSame(5000, $activePrice->comparePrice);
        $this->assertSame(15, $activePrice->discountPercent);
        $this->assertNull($expired->activePromotion);
        $this->assertSame(8000, $expiredPrice->finalPrice);
        $this->assertNull($expiredPrice->discountPercent);
    }

    private function bluePng(): string
    {
        $image = imagecreatetruecolor(64, 64);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 60, 220));
        $path = tempnam(sys_get_temp_dir(), 'twende').'.png';
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }
}
