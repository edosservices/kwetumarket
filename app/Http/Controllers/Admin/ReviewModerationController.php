<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Workflow\ReviewWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewModerationController extends Controller
{
    public function update(Request $request, Review $review, ReviewWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,hide,restore'],
        ]);

        $workflow->moderate($review, $request->user(), $data['decision']);

        return back();
    }
}
