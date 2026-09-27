<?php

namespace App\Services\Catalog;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\CommerceNotice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function record(
        Product $product,
        ?ProductVariant $variant,
        StockMovementType $type,
        int $quantity,
        ?User $actor = null,
        ?string $reference = null,
        ?string $comment = null,
    ): StockMovement {
        if ($quantity === 0) {
            throw new InvalidArgumentException('Stock quantity must not be zero.');
        }

        if ($type !== StockMovementType::Adjustment && $quantity < 0) {
            throw new InvalidArgumentException('Only an adjustment may use a negative quantity.');
        }

        if ($variant !== null && (int) $variant->product_id !== (int) $product->id) {
            throw new InvalidArgumentException('The variant does not belong to this product.');
        }

        return DB::transaction(function () use ($product, $variant, $type, $quantity, $actor, $reference, $comment): StockMovement {
            $inventory = Inventory::query()
                ->where('product_id', $product->id)
                ->when(
                    $variant,
                    fn ($query) => $query->where('product_variant_id', $variant->id),
                    fn ($query) => $query->whereNull('product_variant_id'),
                )
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                $inventory = Inventory::query()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => 0,
                    'reserved' => 0,
                ]);
            }

            $availableBefore = $inventory->available();
            [$onHand, $reserved, $availableDelta] = $this->apply(
                $type,
                $inventory->quantity,
                $inventory->reserved,
                $quantity,
            );

            $inventory->update([
                'quantity' => $onHand,
                'reserved' => $reserved,
            ]);

            if ($variant) {
                $variant->update([
                    'stock' => max(0, $onHand - $reserved),
                ]);
            }

            $availableAfter = max(0, $onHand - $reserved);
            $movement = $inventory->movements()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'user_id' => $actor?->id,
                'type' => $type,
                'quantity' => $availableDelta,
                'quantity_before' => $availableBefore,
                'quantity_after' => $availableAfter,
                'reference' => $reference,
                'comment' => $comment,
            ]);
            $this->alert($product, $availableBefore, $availableAfter);

            return $movement;
        });
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function apply(StockMovementType $type, int $onHand, int $reserved, int $quantity): array
    {
        $units = abs($quantity);

        return match ($type) {
            StockMovementType::Purchase,
            StockMovementType::Return,
            StockMovementType::Cancellation => [$onHand + $units, $reserved, $units],
            StockMovementType::Sale => $this->decreaseOnHand($onHand, $reserved, $units),
            StockMovementType::Adjustment => $this->adjust($onHand, $reserved, $quantity),
            StockMovementType::Reservation => $this->reserve($onHand, $reserved, $units),
            StockMovementType::Release => $this->release($onHand, $reserved, $units),
        };
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function decreaseOnHand(int $onHand, int $reserved, int $units): array
    {
        if (($onHand - $reserved) < $units) {
            throw new InsufficientStockException('Not enough available stock.');
        }

        return [$onHand - $units, $reserved, -$units];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function adjust(int $onHand, int $reserved, int $delta): array
    {
        $next = $onHand + $delta;

        if ($next < $reserved || $next < 0) {
            throw new InsufficientStockException('Adjustment would make stock inconsistent.');
        }

        return [$next, $reserved, $delta];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function reserve(int $onHand, int $reserved, int $units): array
    {
        if (($onHand - $reserved) < $units) {
            throw new InsufficientStockException('Not enough available stock to reserve.');
        }

        return [$onHand, $reserved + $units, -$units];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function alert(Product $product, int $before, int $after): void
    {
        $threshold = (int) config('twende.nearby.low_stock', 3);
        $vendorUser = $product->shop?->vendor?->user;

        if (! $vendorUser) {
            return;
        }

        if ($before > 0 && $after === 0) {
            $vendorUser->notify(new CommerceNotice(
                __('operations.stock_out_title'),
                __('operations.stock_out_body', ['name' => $product->name]),
                route('vendor.inventory.index'),
            ));

            return;
        }

        if ($before > $threshold && $after > 0 && $after <= $threshold) {
            $vendorUser->notify(new CommerceNotice(
                __('operations.stock_low_title'),
                __('operations.stock_low_body', ['name' => $product->name, 'qty' => $after]),
                route('vendor.inventory.index'),
            ));
        }
    }

    private function release(int $onHand, int $reserved, int $units): array
    {
        if ($reserved < $units) {
            throw new InsufficientStockException('Cannot release more than the reserved quantity.');
        }

        return [$onHand, $reserved - $units, $units];
    }
}
