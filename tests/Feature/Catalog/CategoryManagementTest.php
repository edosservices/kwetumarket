<?php

namespace Tests\Feature\Catalog;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_create_and_update_a_category_hierarchy(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $parent = Category::factory()->create(['name' => 'Électronique', 'slug' => 'electronique']);

        $this->actingAs($admin)->post('/admin/categories', [
            'parent_id' => $parent->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Téléphones',
            'status' => 'active',
            'sort_order' => 2,
        ])->assertRedirect('/admin/categories');

        $child = Category::query()->where('slug', 'smartphones')->firstOrFail();

        $this->assertSame($parent->id, $child->parent_id);
        $this->assertTrue($parent->fresh()->children->contains($child));

        $this->actingAs($admin)->put('/admin/categories/smartphones', [
            'parent_id' => $parent->id,
            'name' => 'Téléphones',
            'slug' => 'telephones',
            'status' => 'active',
            'sort_order' => 3,
        ])->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', ['slug' => 'telephones', 'name' => 'Téléphones']);
    }

    public function test_slugs_stay_unique_when_generated(): void
    {
        $first = Category::query()->create(['name' => 'Mode', 'status' => 'active']);
        $second = Category::query()->create(['name' => 'Mode', 'status' => 'active']);

        $this->assertSame('mode', $first->slug);
        $this->assertNotSame($first->slug, $second->slug);
    }

    public function test_public_category_pages_list_the_hierarchy(): void
    {
        $parent = Category::factory()->create(['name' => 'Maison', 'slug' => 'maison']);
        Category::factory()->child($parent)->create(['name' => 'Meubles', 'slug' => 'meubles']);

        $this->get('/categories')->assertOk()->assertSee('Maison');
        $this->get('/categorie/maison')->assertOk()->assertSee('Meubles');
    }
}
