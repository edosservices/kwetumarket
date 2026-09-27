<?php

namespace Tests\Feature;

use App\Contracts\ProductSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_uses_the_catalogue_contract(): void
    {
        $this->assertInstanceOf(ProductSearch::class, app(ProductSearch::class));

        $this->get('/recherche?q=riz')
            ->assertOk()
            ->assertSee('riz');

        $this->get('/recherche?q='.str_repeat('a', 121))
            ->assertSessionHasErrors('q');
    }

    public function test_missing_pages_show_the_official_logo(): void
    {
        $this->get('/cette-page-n-existe-pas')
            ->assertNotFound()
            ->assertSee('twende-market-logo.png', false);
    }
}
