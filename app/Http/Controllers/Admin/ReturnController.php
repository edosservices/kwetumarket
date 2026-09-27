<?php

namespace App\Http\Controllers\Admin;

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
            'decision' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflow->supervise($returnRequest, $request->user(), $data['decision'], $data['admin_note'] ?? null);

        return back();
    }
}
