<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Commerce\CartService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(CartService $carts): JsonResponse
    {
        $quote = $carts->quote();

        return response()->json([
            'data' => [
                'currency' => $quote->currency,
                'subtotal' => $quote->subtotal,
                'discount' => $quote->discount,
                'delivery_fee' => $quote->deliveryFee,
                'tax' => $quote->tax,
                'commission' => $quote->commission,
                'total' => $quote->total,
                'blocked' => $quote->blocked,
                'lines' => collect($quote->lines)->map(fn ($line) => [
                    'id' => $line->item->id,
                    'product_id' => $line->item->product_id,
                    'variant_id' => $line->item->product_variant_id,
                    'name' => $line->item->product?->name,
                    'quantity' => $line->item->quantity,
                    'unit_price' => $line->unitPrice,
                    'line_total' => $line->lineTotal,
                    'available' => $line->available,
                    'short' => $line->short,
                    'formatted_total' => Money::format($line->lineTotal, $quote->currency),
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request, CartService $carts): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $product = Product::query()->findOrFail($data['product_id']);
        $item = $carts->add($product, $data['product_variant_id'] ?? null, (int) ($data['quantity'] ?? 1));

        return response()->json([
            'data' => [
                'id' => $item->id,
                'quantity' => $item->quantity,
            ],
        ], 201);
    }
}
