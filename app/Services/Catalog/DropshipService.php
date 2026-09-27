<?php

namespace App\Services\Catalog;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DropshipService
{
    public function __construct(
        private StockService $stock,
        private PriceHistoryService $prices,
    ) {}

    public function import(Shop $shop, SupplierOffer $offer, User $actor, int $salePrice, bool $publish, bool $sync): Product
    {
        if (! $offer->is_active || (int) $offer->vendor_id === (int) $shop->vendor_id) {
            throw ValidationException::withMessages(['offer' => __('operations.offer_unavailable')]);
        }

        if ($salePrice < 1) {
            throw ValidationException::withMessages(['price' => __('operations.import_price')]);
        }

        return DB::transaction(function () use ($shop, $offer, $actor, $salePrice, $publish, $sync): Product {
            $categoryId = $shop->products()->value('category_id') ?: Category::query()->value('id');

            if (! $categoryId) {
                throw ValidationException::withMessages(['offer' => __('operations.import_category')]);
            }

            $product = Product::query()->create([
                'shop_id' => $shop->id,
                'category_id' => $categoryId,
                'name' => $offer->name,
                'sku' => $this->sku($offer->sku),
                'description' => $offer->description,
                'short_description' => $offer->description,
                'price' => $salePrice,
                'currency' => $offer->currency,
                'status' => $publish ? ProductStatus::Published : ProductStatus::Draft,
                'condition' => ProductCondition::New,
                'is_dropship' => true,
                'supplier_name' => $offer->vendor?->business_name,
                'supplier_sku' => $offer->sku,
                'supplier_price' => $offer->price,
                'supplier_offer_id' => $offer->id,
                'stock_sync' => $sync,
            ]);

            $this->prices->record($product, null, ['price' => null], ['price' => $salePrice], $actor);

            if ($offer->image_path) {
                $target = 'products/'.$product->id.'/'.basename($offer->image_path);
                $disk = $offer->image_disk ?: 'public';

                if (Storage::disk($disk)->exists($offer->image_path)) {
                    Storage::disk($disk)->copy($offer->image_path, $target);
                    $product->images()->create([
                        'disk' => $disk,
                        'path' => $target,
                        'alt_text' => $product->name,
                        'is_primary' => true,
                        'sort_order' => 1,
                    ]);
                }
            }

            $units = $sync && (int) $offer->stock >= (int) $offer->moq ? (int) $offer->stock : 0;

            if ($units > 0) {
                $this->stock->record($product, null, StockMovementType::Purchase, $units, $actor, $offer->sku, 'Import fournisseur');
            }

            return $product;
        });
    }

    public function saleAllowed(Product $product, int $localAvailable): int
    {
        if (! $product->stock_sync || ! $product->supplier_offer_id) {
            return $localAvailable;
        }

        $offer = $product->relationLoaded('supplierOffer')
            ? $product->supplierOffer
            : $product->supplierOffer()->first();

        if (! $offer || ! $offer->is_active) {
            return 0;
        }

        return min($localAvailable, (int) $offer->stock);
    }

    private function sku(string $source): string
    {
        $base = 'DS-'.substr(preg_replace('/[^A-Za-z0-9_-]/', '', $source) ?: 'ITEM', 0, 40);

        do {
            $sku = $base.'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (Product::query()->where('sku', $sku)->exists());

        return $sku;
    }

    public static function minor(string $input): int
    {
        return Money::toMinor($input);
    }
}
