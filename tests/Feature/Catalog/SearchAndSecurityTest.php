<?php

namespace Tests\Feature\Catalog;

use App\Enums\ProductCondition;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Catalog\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_search_filters_sorts_and_paginates(): void
    {
        $shop = Shop::factory()->create(['name' => 'Kinois Tech', 'slug' => 'kinois-tech']);
        $phones = Category::factory()->create(['name' => 'Smartphones', 'slug' => 'smartphones']);
        $brand = Brand::factory()->create(['name' => 'Tecno', 'slug' => 'tecno']);
        $other = Category::factory()->create();

        $cheap = $this->publish($shop, $phones, $brand, 'Tecno Spark', 'tecno-spark', 'TEC-1', 50000, ProductCondition::New);
        $pricey = $this->publish($shop, $phones, $brand, 'Tecno Camon', 'tecno-camon', 'TEC-2', 900000, ProductCondition::Used);
        $this->publish($shop, $other, null, 'Table basse', 'table-basse', 'MEU-1', 500000, ProductCondition::New);

        app(StockService::class)->record($cheap, null, StockMovementType::Purchase, 2);

        $this->get('/produits?q=Tecno&sort=price_asc')
            ->assertOk()
            ->assertSeeInOrder(['Tecno Spark', 'Tecno Camon'])
            ->assertDontSee('Table basse');

        $this->get('/produits?category='.$phones->id.'&brand='.$brand->id.'&condition=used&price_min=1000&price_max=20000')
            ->assertOk()
            ->assertSee('Tecno Camon')
            ->assertDontSee('Tecno Spark');

        $this->get('/produits?availability=in_stock')
            ->assertOk()
            ->assertSee('Tecno Spark')
            ->assertDontSee('Tecno Camon');

        $this->get('/recherche?q=Kinois')
            ->assertOk()
            ->assertSee('Tecno Spark');

        Product::factory()->published()->count(13)->create([
            'shop_id' => $shop->id,
            'category_id' => $other->id,
            'name' => 'Lot pagination',
        ]);

        $this->get('/produits?q=Lot%20pagination&page=2')
            ->assertOk()
            ->assertSee('2 /', false);
    }

    public function test_clients_and_guests_cannot_manage_the_catalogue(): void
    {
        $client = User::factory()->withRole(UserRole::Client)->create();

        $this->get('/vendeur/produits')->assertRedirect(route('login'));
        $this->post('/vendeur/produits', [])->assertRedirect(route('login'));
        $this->get('/admin/categories')->assertRedirect(route('login'));

        $this->actingAs($client)->get('/admin')->assertForbidden();
        $this->actingAs($client)->get('/admin/categories')->assertForbidden();
        $this->actingAs($client)->get('/admin/products')->assertForbidden();
        $this->actingAs($client)->get('/vendeur/produits')->assertForbidden();
    }

    public function test_inventory_movements_are_limited_to_the_owning_vendor(): void
    {
        $owner = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $shop = Shop::factory()->create(['vendor_id' => $owner->id]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => Category::factory(),
        ]);

        $this->actingAs($owner->user)->post('/vendeur/stock', [
            'product_id' => $product->id,
            'type' => 'purchase',
            'quantity' => 5,
            'comment' => 'Réception',
        ])->assertRedirect();

        $this->assertSame(5, $product->fresh()->availableQuantity());

        $this->actingAs($other->user)->post('/vendeur/stock', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => -5,
        ])->assertForbidden();

        $this->assertSame(5, $product->fresh()->availableQuantity());
    }

    public function test_public_api_exposes_only_published_catalogue_records(): void
    {
        $shop = Shop::factory()->create(['name' => 'Maison Amina', 'slug' => 'maison-amina']);
        $category = Category::factory()->create(['name' => 'Meubles', 'slug' => 'meubles']);
        $product = $this->publish($shop, $category, null, 'Table basse', 'table-basse', 'MEU-API', 1000, ProductCondition::New);
        Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Brouillon secret',
            'slug' => 'brouillon-secret',
            'status' => 'draft',
        ]);

        $this->getJson('/api/v1/categories')->assertOk()->assertJsonFragment(['slug' => 'meubles']);
        $this->getJson('/api/v1/categories/meubles')->assertOk()->assertJsonPath('data.name', 'Meubles');
        $this->getJson('/api/v1/shops')->assertOk()->assertJsonFragment(['slug' => 'maison-amina']);
        $this->getJson('/api/v1/shops/maison-amina')->assertOk()->assertJsonPath('data.name', 'Maison Amina');
        $this->getJson('/api/v1/products')->assertOk()->assertJsonFragment(['slug' => 'table-basse']);
        $this->getJson('/api/v1/products/table-basse')->assertOk()->assertJsonPath('data.sku', $product->sku);
        $this->getJson('/api/v1/products/brouillon-secret')->assertNotFound();
    }

    private function publish(Shop $shop, Category $category, ?Brand $brand, string $name, string $slug, string $sku, int $price, ProductCondition $condition): Product
    {
        return Product::factory()->published()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'price' => $price,
            'condition' => $condition,
        ]);
    }
}
