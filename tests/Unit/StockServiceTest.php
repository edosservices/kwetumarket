<?php

namespace Tests\Unit;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Services\Catalog\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_movements_trace_available_stock(): void
    {
        $product = $this->product();
        $stock = app(StockService::class);

        $stock->record($product, null, StockMovementType::Purchase, 10, null, 'PO-1');
        $stock->record($product, null, StockMovementType::Sale, 3, null, 'SO-1');
        $stock->record($product, null, StockMovementType::Reservation, 2, null, 'RS-1');
        $stock->record($product, null, StockMovementType::Release, 1, null, 'RL-1');
        $stock->record($product, null, StockMovementType::Return, 1, null, 'RT-1');

        $inventory = $product->inventories()->firstOrFail();

        $this->assertSame(8, $inventory->quantity);
        $this->assertSame(1, $inventory->reserved);
        $this->assertSame(7, $inventory->available());
        $this->assertSame(5, $product->stockMovements()->count());
        $this->assertSame(0, $product->stockMovements()->first()->quantity_before);
        $this->assertSame(10, $product->stockMovements()->first()->quantity_after);
    }

    public function test_a_sale_cannot_exceed_available_stock(): void
    {
        $product = $this->product();
        $stock = app(StockService::class);
        $stock->record($product, null, StockMovementType::Purchase, 2);

        $this->expectException(InsufficientStockException::class);

        $stock->record($product, null, StockMovementType::Sale, 3);
    }

    public function test_variant_stock_follows_its_inventory(): void
    {
        $product = $this->product();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'VAR-UNIQUE-1',
            'stock' => 0,
        ]);

        app(StockService::class)->record($product, $variant, StockMovementType::Purchase, 6, null, $variant->sku);

        $this->assertSame(6, $variant->fresh()->stock);
        $this->assertSame(1, $variant->inventory()->count());
    }

    private function product(): Product
    {
        return Product::factory()->published()->create([
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
        ]);
    }
}
