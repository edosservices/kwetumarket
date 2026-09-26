<?php

namespace Tests\Feature\Catalog;

use App\Enums\ShopStatus;
use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_active_vendor_can_create_a_public_shop(): void
    {
        $vendor = Vendor::factory()->create();

        $this->actingAs($vendor->user)->put('/vendeur/boutique', [
            'name' => 'Kinois Tech',
            'slug' => 'kinois-tech',
            'description' => 'Téléphones et accessoires.',
            'location' => 'Gombe, Kinshasa',
            'phone' => '+243810000111',
            'email' => 'kinois@twende.market',
            'status' => 'active',
        ])->assertRedirect('/vendeur/boutique');

        $shop = Shop::query()->where('slug', 'kinois-tech')->firstOrFail();

        $this->assertSame($vendor->id, $shop->vendor_id);
        $this->assertSame(ShopStatus::Pending, $shop->status);

        $shop->update(['status' => ShopStatus::Active]);

        $this->get('/boutique/kinois-tech')
            ->assertOk()
            ->assertSee('Kinois Tech')
            ->assertSee('Gombe, Kinshasa');

        $this->get('/boutiques')->assertOk()->assertSee('Kinois Tech');
    }

    public function test_a_client_cannot_open_the_vendor_shop_editor(): void
    {
        $client = User::factory()->withRole(UserRole::Client)->create();

        $this->actingAs($client)->get('/vendeur/boutique')->assertForbidden();
    }
}
