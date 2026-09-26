<?php

namespace Tests\Feature\Catalog;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_vendor_creates_a_draft_and_an_admin_publishes_it(): void
    {
        [$vendor, $shop, $category] = $this->catalog();
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($vendor->user)->post('/vendeur/produits', $this->payload($shop, $category, [
            'name' => 'Tecno Spark 20',
            'slug' => 'tecno-spark-20',
            'sku' => 'TEC-SP20',
            'status' => 'published',
        ]))->assertSessionHasErrors('status');

        $this->actingAs($vendor->user)->post('/vendeur/produits', $this->payload($shop, $category, [
            'name' => 'Tecno Spark 20',
            'slug' => 'tecno-spark-20',
            'sku' => 'TEC-SP20',
            'status' => 'draft',
            'initial_stock' => 4,
        ]))->assertRedirect();

        $product = Product::query()->where('sku', 'TEC-SP20')->firstOrFail();

        $this->assertSame(ProductStatus::Draft, $product->status);
        $this->assertSame(4, $product->fresh()->availableQuantity());

        auth()->logout();

        $this->get('/produit/tecno-spark-20')->assertNotFound();

        $this->actingAs($admin)->put('/admin/products/tecno-spark-20', $this->payload($shop, $category, [
            'name' => 'Tecno Spark 20',
            'slug' => 'tecno-spark-20',
            'sku' => 'TEC-SP20',
            'status' => 'published',
        ]))->assertRedirect();

        $this->assertSame(ProductStatus::Published, $product->fresh()->status);
        $this->get('/produit/tecno-spark-20')->assertOk()->assertSee('Tecno Spark 20')->assertSee('Ajouter au panier');
        $this->get('/produits')->assertOk()->assertSee('Tecno Spark 20');
    }

    public function test_sku_and_slug_must_be_unique(): void
    {
        [$vendor, $shop, $category] = $this->catalog();
        Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'slug' => 'deja-pris',
            'sku' => 'SKU-PRIS',
        ]);

        $this->actingAs($vendor->user)->post('/vendeur/produits', $this->payload($shop, $category, [
            'slug' => 'deja-pris',
            'sku' => 'SKU-PRIS',
        ]))->assertSessionHasErrors(['slug', 'sku']);
    }

    public function test_a_vendor_cannot_edit_another_vendors_product(): void
    {
        [$owner, $shop, $category] = $this->catalog();
        $intruder = Vendor::factory()->create();
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'slug' => 'produit-prive',
            'sku' => 'PRIVE-1',
        ]);

        $this->actingAs($intruder->user)
            ->put('/vendeur/produits/produit-prive', $this->payload($shop, $category, [
                'name' => 'Piraté',
                'slug' => 'produit-prive',
                'sku' => 'PRIVE-1',
            ]))
            ->assertForbidden();

        $this->assertSame($product->name, $product->fresh()->name);
        $this->assertNotNull($owner->id);
    }

    public function test_valid_and_invalid_image_uploads(): void
    {
        Storage::fake('public');
        [$vendor, $shop, $category] = $this->catalog();
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'slug' => 'avec-image',
        ]);

        $this->actingAs($vendor->user)
            ->post('/vendeur/produits/avec-image/images', [
                'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('image');

        $this->actingAs($vendor->user)
            ->post('/vendeur/produits/avec-image/images', [
                'image' => UploadedFile::fake()->image('phone.jpg', 40, 40),
                'alt_text' => 'Tecno',
            ])
            ->assertRedirect();

        $image = $product->images()->firstOrFail();

        $this->assertTrue($image->is_primary);
        $this->assertNotSame('phone.jpg', basename($image->path));
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_variants_require_a_unique_sku_and_track_stock(): void
    {
        [$vendor, $shop, $category] = $this->catalog();
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'slug' => 'iphone-15',
            'sku' => 'IPH-15',
        ]);

        $this->actingAs($vendor->user)->post('/vendeur/produits/iphone-15/variantes', [
            'name' => 'Noir / 128 GB',
            'sku' => 'IPH-15-NOIR-128',
            'stock' => 3,
            'status' => 'active',
            'attributes' => ['color' => 'Noir', 'capacity' => '128 GB'],
        ])->assertRedirect();

        $this->actingAs($vendor->user)->post('/vendeur/produits/iphone-15/variantes', [
            'name' => 'Doublon',
            'sku' => 'IPH-15-NOIR-128',
            'stock' => 1,
            'status' => 'active',
        ])->assertSessionHasErrors('sku');

        $variant = $product->variants()->firstOrFail();

        $this->assertSame(3, $variant->stock);
        $this->assertSame('Noir', $variant->attributes['color']);
    }

    /**
     * @return array{0: Vendor, 1: Shop, 2: Category}
     */
    private function catalog(): array
    {
        $vendor = Vendor::factory()->create();
        $shop = Shop::factory()->create(['vendor_id' => $vendor->id]);
        $category = Category::factory()->create();

        return [$vendor, $shop, $category];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Shop $shop, Category $category, array $overrides = []): array
    {
        return array_merge([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Produit test',
            'sku' => 'SKU-TEST',
            'description' => 'Une description assez complète pour le catalogue.',
            'price' => '1500.00',
            'currency' => 'CDF',
            'status' => 'draft',
            'condition' => 'new',
        ], $overrides);
    }
}
