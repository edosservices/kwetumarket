<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Workflow\ReturnWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function store(Request $request, Order $order, ReturnWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $workflow->request($order, $request->user(), $data['reason']);

        return back();
    }
}
