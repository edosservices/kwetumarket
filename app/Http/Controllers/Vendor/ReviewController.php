<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Workflow\ReviewWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function reply(Request $request, Review $review, ReviewWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'vendor_reply' => ['required', 'string', 'max:1000'],
        ]);

        $workflow->reply($review, $request->user(), $data['vendor_reply']);

        return back();
    }
}
