<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CatalogSearchRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Search\EloquentProductSearch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(CatalogSearchRequest $request, EloquentProductSearch $search): AnonymousResourceCollection
    {
        return ProductResource::collection($search->search($request->term(), $request->filters())['paginator']);
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->isPubliclyVisible() || request()->user()?->can('view', $product), 404);

        $product->load(['shop', 'category', 'brand', 'primaryImage', 'images', 'variants']);
        $product->loadSum('inventories as stock_on_hand', 'quantity');
        $product->loadSum('inventories as stock_reserved', 'reserved');

        return new ProductResource($product);
    }
}
