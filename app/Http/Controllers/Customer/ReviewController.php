<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\Workflow\ReviewWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, ReviewWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflow->save(
            $request->user(),
            Product::query()->findOrFail($data['product_id']),
            (int) $data['rating'],
            $data['body'] ?? null,
        );

        return back();
    }

    public function update(Request $request, Review $review, ReviewWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflow->update($review, $request->user(), (int) $data['rating'], $data['body'] ?? null);

        return back();
    }

    public function destroy(Request $request, Review $review, ReviewWorkflow $workflow): RedirectResponse
    {
        $workflow->delete($review, $request->user());

        return back();
    }
}
