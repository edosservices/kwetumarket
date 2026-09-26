<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId, 403);

        $products = Product::query()
            ->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendorId))
            ->with(['shop', 'category', 'brand', 'primaryImage'])
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved')
            ->latest()
            ->paginate(20);

        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->productAttributes());
        $product->load(['shop', 'category', 'brand']);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        abort_unless($request->user()->can('view', $product), 403);
        abort_unless((int) $product->shop->vendor_id === (int) $request->user()->vendorProfile?->id, 403);

        $product->load(['shop', 'category', 'brand', 'images', 'variants', 'primaryImage']);

        return new ProductResource($product);
    }

    public function update(ProductRequest $request, Product $product): ProductResource
    {
        abort_unless((int) $product->shop->vendor_id === (int) $request->user()->vendorProfile?->id, 403);
        $product->update($request->productAttributes());
        $product->load(['shop', 'category', 'brand', 'primaryImage']);

        return new ProductResource($product);
    }
}
