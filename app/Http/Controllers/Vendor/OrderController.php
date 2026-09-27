<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Workflow\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, Order $order, OrderWorkflow $workflow): View
    {
        $this->authorize('view', $order);

        return view('pages.vendor.orders.show', [
            'order' => $order->load('items.product'),
            'options' => $workflow->options($order, $request->user()),
        ]);
    }

    public function update(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:30'],
        ]);

        $workflow->transition($order, $request->user(), $data['status']);

        return redirect()->route('vendor.orders.show', $order);
    }
}
