<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('orders.view'), 403);

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(12);

        return view('pages.customer.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        return view('pages.customer.orders.show', [
            'order' => $order->load('items.product'),
        ]);
    }
}
