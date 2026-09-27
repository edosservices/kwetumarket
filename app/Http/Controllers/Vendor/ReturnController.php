<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\Workflow\ReturnWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function decide(Request $request, ReturnRequest $returnRequest, ReturnWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:vendor_accepted,vendor_rejected'],
            'vendor_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflow->vendorDecide($returnRequest, $request->user(), $data['decision'], $data['vendor_note'] ?? null);

        return back();
    }
}
