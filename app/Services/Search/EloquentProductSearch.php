<?php

namespace App\Services\Search;

use App\Contracts\ProductSearch;
use App\Enums\ProductCondition;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentProductSearch implements ProductSearch
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<int, mixed>, total: int, paginator: LengthAwarePaginator}
     */
    public function search(string $query, array $filters = []): array
    {
        $builder = Product::query()->forCard()->published();
        $term = trim($query);

        if ($term !== '') {
            $like = '%'.$this->escapeLike($term).'%';
            $builder->where(function (Builder $products) use ($like): void {
                $products
                    ->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', $like))
                    ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                    ->orWhereHas('shop', fn (Builder $shop) => $shop->where('name', 'like', $like));
            });
        }

        if (! empty($filters['category'])) {
            $builder->whereIn('category_id', Category::idsWithDescendants((int) $filters['category']));
        }

        if (! empty($filters['brand'])) {
            $builder->where('brand_id', (int) $filters['brand']);
        }

        if (! empty($filters['shop'])) {
            $builder->where('shop_id', (int) $filters['shop']);
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== null && $filters['price_min'] !== '') {
            $builder->where('price', '>=', (int) $filters['price_min']);
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== null && $filters['price_max'] !== '') {
            $builder->where('price', '<=', (int) $filters['price_max']);
        }

        if (! empty($filters['condition']) && in_array($filters['condition'], ProductCondition::values(), true)) {
            $builder->where('condition', $filters['condition']);
        }

        $availability = $filters['availability'] ?? null;
        $stockSql = '(select coalesce(sum(quantity - reserved), 0) from inventories where inventories.product_id = products.id)';

        if ($availability === 'in_stock') {
            $builder->whereRaw($stockSql.' > 0');
        } elseif ($availability === 'out_of_stock') {
            $builder->whereRaw($stockSql.' <= 0');
        }

        if (! empty($filters['min_rating'])) {
            $builder->whereRaw(
                '(select avg(rating) from reviews where reviews.product_id = products.id) >= ?',
                [(int) $filters['min_rating']]
            );
        }

        if (! empty($filters['city'])) {
            $city = (string) $filters['city'];
            $builder->whereHas('shop', fn (Builder $shop) => $shop->where('city', $city));
        }

        $this->applySort($builder, (string) ($filters['sort'] ?? 'relevance'), $term);

        $perPage = max(1, min(48, (int) ($filters['per_page'] ?? 12)));
        $paginator = $builder->paginate($perPage)->withQueryString();

        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'paginator' => $paginator,
        ];
    }

    private function applySort(Builder $builder, string $sort, string $term): void
    {
        if ($sort === 'price_asc') {
            $builder->orderBy('price')->orderByDesc('products.created_at');

            return;
        }

        if ($sort === 'price_desc') {
            $builder->orderByDesc('price')->orderByDesc('products.created_at');

            return;
        }

        if ($sort === 'newest') {
            $builder->orderByDesc('products.created_at');

            return;
        }

        if ($sort === 'rating') {
            $builder->orderByDesc('reviews_avg_rating')->orderByDesc('products.created_at');

            return;
        }

        if ($sort === 'bestsellers') {
            $builder->withSum('orderItems as units_sold', 'quantity')
                ->orderByRaw('coalesce(units_sold, 0) desc')
                ->orderByDesc('products.created_at');

            return;
        }

        if ($term !== '') {
            $prefix = $this->escapeLike($term).'%';
            $builder->orderByRaw('case when products.name like ? then 0 when products.sku = ? then 1 else 2 end', [$prefix, $term]);
        }

        $builder->orderByDesc('products.created_at');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
