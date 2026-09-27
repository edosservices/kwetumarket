<?php

namespace App\Http\Controllers\Admin;

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

        return view('pages.admin.orders.show', [
            'order' => $order->load('items.product', 'customer'),
            'options' => $workflow->options($order, $request->user()),
        ]);
    }

    public function update(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:30'],
        ]);

        $workflow->transition($order, $request->user(), $data['status']);

        return redirect()->route('admin.orders.show', $order);
    }
}
