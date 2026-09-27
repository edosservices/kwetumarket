<?php

namespace App\Services\Catalog;

use App\Contracts\CatalogImporter;
use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CsvCatalogImporter implements CatalogImporter
{
    public function __construct(private StockService $stock) {}

    public function import(Shop $shop, string $contents, User $actor): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($contents)) ?: [];
        $lines = array_values(array_filter($lines, fn (string $line) => trim($line) !== ''));

        if ($lines === []) {
            throw ValidationException::withMessages(['file' => __('operations.import_empty')]);
        }

        $header = array_map(fn (string $column) => strtolower(trim($column)), str_getcsv(array_shift($lines)));

        foreach (['name', 'sku', 'description', 'price'] as $column) {
            if (! in_array($column, $header, true)) {
                throw ValidationException::withMessages(['file' => __('operations.import_columns')]);
            }
        }

        $created = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $cells = str_getcsv($line);
            $row = [];

            foreach ($header as $position => $column) {
                $row[$column] = trim((string) ($cells[$position] ?? ''));
            }

            try {
                DB::transaction(function () use ($shop, $actor, $row): void {
                    $price = Money::toMinor($row['price']);
                    $categoryId = $shop->products()->value('category_id') ?: Category::query()->value('id');

                    if ($price < 1) {
                        throw ValidationException::withMessages(['price' => __('operations.import_price')]);
                    }

                    if (! $categoryId) {
                        throw ValidationException::withMessages(['file' => __('operations.import_category')]);
                    }

                    $product = Product::query()->create([
                        'shop_id' => $shop->id,
                        'category_id' => $categoryId,
                        'name' => $row['name'],
                        'sku' => $row['sku'],
                        'description' => $row['description'],
                        'short_description' => ($row['short_description'] ?? '') !== '' ? $row['short_description'] : null,
                        'price' => $price,
                        'currency' => ($row['currency'] ?? '') !== '' ? $row['currency'] : 'CDF',
                        'status' => ProductStatus::Draft,
                        'condition' => ProductCondition::New,
                    ]);

                    $opening = (int) ($row['stock'] ?? 0);

                    if ($opening > 0) {
                        $this->stock->record($product, null, StockMovementType::Purchase, $opening, $actor, $product->sku, 'Import CSV');
                    }
                });
                $created++;
            } catch (Throwable $exception) {
                $errors[] = __('operations.import_line', ['line' => $index + 2, 'message' => $exception->getMessage()]);
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }
}
