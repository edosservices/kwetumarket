<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()?->can('products.manage'), 403);

        $products = Product::query()
            ->with(['shop', 'category', 'brand', 'primaryImage'])
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved')
            ->latest()
            ->paginate(20);

        return ProductResource::collection($products);
    }
}
