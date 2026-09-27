<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\Workflow\ReviewWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewReportController extends Controller
{
    public function store(Request $request, Review $review, ReviewWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $workflow->report($review, $request->user(), $data['reason'] ?? null);

        return back();
    }
}
