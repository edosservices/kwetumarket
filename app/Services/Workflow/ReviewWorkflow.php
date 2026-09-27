<?php

namespace App\Services\Workflow;

use App\Models\AuditLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewWorkflow
{
    public function save(User $customer, Product $product, int $rating, ?string $body, ?int $orderId = null): Review
    {
        $bought = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', $customer->id))
            ->exists();

        abort_unless($bought, 403);

        $existing = Review::query()
            ->where('user_id', $customer->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing && in_array($existing->status, ['hidden', 'reported'], true)) {
            abort(403);
        }

        return Review::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'product_id' => $product->id,
            ],
            [
                'order_id' => $orderId,
                'rating' => $rating,
                'body' => $body,
                'status' => 'pending',
            ],
        );
    }

    public function update(Review $review, User $customer, int $rating, ?string $body): void
    {
        abort_unless((int) $review->user_id === (int) $customer->id, 403);
        abort_unless(in_array($review->status, ['pending', 'published'], true), 403);

        $review->fill([
            'rating' => $rating,
            'body' => $body,
            'status' => 'pending',
        ])->save();
    }

    public function delete(Review $review, User $customer): void
    {
        abort_unless((int) $review->user_id === (int) $customer->id, 403);
        abort_unless(in_array($review->status, ['pending', 'published'], true), 403);

        $review->delete();
    }

    public function reply(Review $review, User $vendor, string $reply): void
    {
        abort_unless($vendor->can('products.view') && $vendor->isVendorSide(), 403);

        $review->loadMissing('product');
        abort_unless($review->product && (int) $review->product->vendor_id === (int) $vendor->vendorId(), 403);

        $review->forceFill([
            'vendor_reply' => $reply,
            'vendor_replied_at' => now(),
        ])->save();

        AuditLog::record($vendor, 'review.reply', $review, [
            'module' => 'reviews',
            'product_id' => $review->product_id,
        ]);
    }

    public function report(Review $review, User $actor, ?string $reason = null): void
    {
        abort_unless((int) $review->user_id !== (int) $actor->id, 403);

        if ($review->status !== 'published') {
            throw ValidationException::withMessages([
                'review' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $review->update(['status' => 'reported']);

        ReviewReport::query()->updateOrCreate(
            ['review_id' => $review->id, 'user_id' => $actor->id],
            ['reason' => $reason],
        );
    }

    public function moderate(Review $review, User $actor, string $decision): void
    {
        abort_unless($actor->canModerateReviews(), 403);

        $from = $review->status;
        $to = match ($decision) {
            'approve' => in_array($from, ['pending', 'reported'], true) ? 'published' : null,
            'hide' => in_array($from, ['pending', 'published', 'reported'], true) ? 'hidden' : null,
            'restore' => in_array($from, ['hidden', 'reported'], true) ? 'published' : null,
            default => null,
        };

        if ($to === null) {
            throw ValidationException::withMessages([
                'decision' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $review->update(['status' => $to]);

        AuditLog::record($actor, 'review.moderate', $review, [
            'module' => 'reviews',
            'from' => $from,
            'to' => $to,
            'decision' => $decision,
        ]);
    }
}
