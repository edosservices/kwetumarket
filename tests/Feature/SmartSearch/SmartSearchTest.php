<?php

namespace Tests\Feature\SmartSearch;

use App\Enums\SocialPlatform;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\ImageSearch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SmartSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_valid_image_search_uses_the_limited_fallback(): void
    {
        [$stocked] = $this->blueShops();

        $response = $this->post('/recherche/image', [
            'image' => $this->blueUpload(),
            'lat' => 0,
            'lng' => 0,
        ]);

        $response->assertRedirect();
        $search = ImageSearch::query()->firstOrFail();
        $this->assertTrue($search->limited);
        $this->assertSame('local', $search->provider);
        $this->assertFalse(Schema::hasColumn('image_searches', 'latitude'));

        $page = $this->get($response->headers->get('Location'));
        $page->assertOk();
        $page->assertSee('Recherche intelligente temporairement limitée.');
        $page->assertSee('Produit similaire');
        $page->assertSee('Couleur dominante : bleu');
        $page->assertSee($stocked->name);
        $page->assertSee('Boutique la plus proche');
        $page->assertDontSee('OpenAI');
        $page->assertDontSee('Table en bois');

        $html = $page->getContent();
        $this->assertMatchesRegularExpression('/Boutique la plus proche.{0,500}'.$stocked->shop->name.'/s', $html);
        $this->assertDoesNotMatchRegularExpression('/Boutique la plus proche.{0,180}Atelier Vide/s', $html);
    }

    public function test_invalid_and_oversized_images_are_rejected(): void
    {
        $this->post('/recherche/image', [
            'image' => UploadedFile::fake()->create('notes.txt', 12, 'text/plain'),
        ])->assertSessionHasErrors('image');

        $this->post('/recherche/image', [
            'image' => UploadedFile::fake()->image('tiny.png', 16, 16),
        ])->assertSessionHasErrors('image');

        $this->post('/recherche/image', [
            'image' => UploadedFile::fake()->image('huge.png', 64, 64)->size(5000),
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, ImageSearch::query()->count());
    }

    public function test_image_search_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $this->post('/recherche/image', [
                'image' => $this->blueUpload(),
            ])->assertRedirect();
        }

        $this->post('/recherche/image', [
            'image' => $this->blueUpload(),
        ])->assertStatus(429);
    }

    public function test_missing_vision_credentials_do_not_crash(): void
    {
        config([
            'twende.vision.driver' => 'openai',
            'twende.vision.openai.key' => null,
        ]);

        $this->post('/recherche/image', [
            'image' => $this->blueUpload(),
        ])->assertRedirect();

        $this->assertSame('local', ImageSearch::query()->firstOrFail()->provider);
    }

    public function test_nearby_search_sorts_by_distance_and_survives_without_location(): void
    {
        $near = $this->place('Pres', 0, 0.01, 'Cahier local', 4, 9000);
        $far = $this->place('Loin', 0, 0.04, 'Cahier lointain', 12, 3000);

        $this->get('/pres-de-moi')
            ->assertOk()
            ->assertSee('Autoriser ma position')
            ->assertSee('Continuer sans localisation')
            ->assertSee('Activez votre localisation pour voir les distances et les boutiques proches.')
            ->assertDontSee('Cahier local');

        $page = $this->get('/pres-de-moi?lat=0&lng=0&radius=10&sort=nearest');
        $page->assertOk()->assertSee('1,1 km')->assertSee('Cahier local');

        $byDistance = collect($this->getJson('/api/v1/products/nearby?lat=0&lng=0&radius=10&sort=nearest')->assertOk()->json('data.offers'))
            ->pluck('product.name')
            ->all();
        $this->assertSame(['Cahier local', 'Cahier lointain'], $byDistance);

        $byPrice = collect($this->getJson('/api/v1/products/nearby?lat=0&lng=0&radius=10&sort=price')->assertOk()->json('data.offers'))
            ->pluck('product.name')
            ->all();
        $this->assertSame(['Cahier lointain', 'Cahier local'], $byPrice);

        $this->assertNotNull($near);
        $this->assertNotNull($far);
    }

    public function test_expired_promotions_disappear_and_nearby_ones_remain(): void
    {
        $near = $this->place('Promo', 0, 0.01, 'Lampe bleue', 6, 10000);
        $near->promotions()->create([
            'promotional_price' => 8000,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        $old = $this->place('Finie', 0, 0.01, 'Lampe ancienne', 6, 10000);
        $old->promotions()->create([
            'promotional_price' => 1000,
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subMinute(),
        ]);

        $this->get('/promotions')
            ->assertOk()
            ->assertSee('Lampe bleue')
            ->assertDontSee('Lampe ancienne');

        $this->get('/promotions?lat=0&lng=0&radius=2')
            ->assertOk()
            ->assertSee('Promotions à moins de 2 km')
            ->assertSee('Lampe bleue')
            ->assertDontSee('Lampe ancienne');

        $this->getJson('/api/v1/promotions/nearby?lat=0&lng=0&radius=2')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Lampe bleue'])
            ->assertJsonMissing(['name' => 'Lampe ancienne']);
    }

    public function test_vendor_can_save_location_and_only_real_social_links_are_shown(): void
    {
        $vendor = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $shop = Shop::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Mode Plus', 'slug' => 'mode-plus']);
        Shop::factory()->create(['vendor_id' => $other->id, 'slug' => 'autre-boutique']);

        $this->actingAs($vendor->user)->put('/vendeur/boutique', [
            'name' => 'Mode Plus',
            'slug' => 'mode-plus',
            'phone' => '+243810000444',
            'email' => 'mode@twende.market',
            'location' => 'Lingwala, Kinshasa',
            'status' => 'active',
            'latitude' => -4.331,
            'longitude' => 15.301,
            'city' => 'Kinshasa',
            'commune' => 'Lingwala',
            'whatsapp' => '+243810000444',
            'instagram' => 'modeplus',
            'publish_location' => '1',
            'publish_address' => '1',
            'business_name' => 'Mode Plus',
            'manager_name' => 'David Ilunga',
        ])->assertRedirect('/vendeur/boutique');

        $shop->refresh();
        $this->assertEquals(-4.331, (float) $shop->latitude);
        $this->assertTrue($shop->publish_location);
        $this->assertDatabaseHas('vendor_social_links', [
            'vendor_id' => $vendor->id,
            'platform' => SocialPlatform::Instagram->value,
            'username' => 'modeplus',
        ]);

        $shop->update(['status' => 'active']);

        $this->get('/boutique/mode-plus')
            ->assertOk()
            ->assertSee('tel:+243810000444', false)
            ->assertSee('https://wa.me/243810000444', false)
            ->assertSee('https://instagram.com/modeplus', false)
            ->assertDontSee('tiktok.com')
            ->assertDontSee('facebook.com');

        Sanctum::actingAs($other->user);
        $this->postJson('/api/v1/shops/mode-plus/location', [
            'latitude' => 1,
            'longitude' => 1,
        ])->assertForbidden();

        $shop->refresh();
        $this->assertEquals(-4.331, (float) $shop->latitude);
    }

    public function test_private_client_details_stay_private_and_nearby_api_hides_unpublished_coordinates(): void
    {
        $owner = User::factory()->withRole(UserRole::Client)->create([
            'address' => '12 avenue secrete du client',
            'quarter' => 'Quartier Secret',
        ]);
        $visitor = User::factory()->withRole(UserRole::Client)->create();
        $shop = $this->place('Cachee', 0, 0.01, 'Sac bleu', 3, 4000)->shop;
        $shop->update(['publish_location' => false, 'publish_address' => false, 'address_line' => 'porte 4']);

        $this->actingAs($owner)->get('/profil')->assertSee('12 avenue secrete du client');
        $this->actingAs($visitor)->get('/')->assertDontSee('12 avenue secrete du client');
        $this->actingAs($visitor)->get('/profil')->assertDontSee('12 avenue secrete du client');

        Sanctum::actingAs($visitor);
        $this->getJson('/api/v1/user')->assertOk()->assertDontSee('12 avenue secrete du client');

        $this->getJson('/api/v1/shops/nearby?lat=0&lng=0&radius=5')
            ->assertOk()
            ->assertJsonMissing(['latitude' => (float) $shop->latitude])
            ->assertJsonMissing(['address' => 'porte 4'])
            ->assertDontSee('12 avenue secrete');
    }

    public function test_expired_search_images_are_pruned(): void
    {
        Storage::disk('public')->put('search-images/old.png', 'image');
        $search = ImageSearch::query()->create([
            'uuid' => (string) str()->uuid(),
            'disk' => 'public',
            'image_path' => 'search-images/old.png',
            'provider' => 'local',
            'limited' => true,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('smart-search:prune')->assertSuccessful();

        $this->assertModelMissing($search);
        Storage::disk('public')->assertMissing('search-images/old.png');
    }

    public function test_public_pages_include_the_developer_credit(): void
    {
        $this->get('/')->assertOk()->assertSee('Développé par Édouard Bengehya');
        $this->get('/a-propos')->assertOk()->assertSee('Développé par Édouard Bengehya')->assertSee('marketplace');
        $this->get('/contact')->assertOk();
        $this->get('/conditions')->assertOk();
        $this->get('/confidentialite')->assertOk();
        $this->get('/vendre')->assertOk();
        $this->get('/aide')->assertOk();
        $this->get('/faq')->assertOk();
    }

    /**
     * @return array{0: Product, 1: Product}
     */
    private function blueShops(): array
    {
        $empty = $this->place('Vide', 0, 0, 'Tissu bleu', 0, 3500);
        $stocked = $this->place('Bleu', 0, 0.02, 'Robe bleue', 8, 4500);
        $this->place('Bois', 0, 0.02, 'Table en bois', 5, 7000);

        return [$stocked, $empty];
    }

    private function place(string $suffix, float $lat, float $lng, string $productName, int $stock, int $price): Product
    {
        $category = Category::factory()->create(['name' => 'Rayon '.$suffix, 'slug' => 'rayon-'.strtolower($suffix).'-'.fake()->unique()->numerify('###')]);
        $shop = Shop::factory()->create([
            'name' => 'Atelier '.$suffix,
            'slug' => 'atelier-'.strtolower($suffix).'-'.fake()->unique()->numerify('###'),
            'latitude' => $lat,
            'longitude' => $lng,
            'publish_location' => true,
            'publish_address' => true,
            'phone' => '+243810000555',
        ]);
        $product = Product::factory()->published()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => $productName,
            'description' => 'Article de '.$suffix,
            'price' => $price,
            'compare_at_price' => null,
        ]);

        if ($stock > 0) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'quantity' => $stock,
                'reserved' => 0,
            ]);
        }

        return $product->load('shop');
    }

    private function blueUpload(): UploadedFile
    {
        $image = imagecreatetruecolor(64, 64);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 60, 220));
        $path = tempnam(sys_get_temp_dir(), 'twende');
        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, 'photo.png', 'image/png', null, true);
    }
}
