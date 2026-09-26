<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\Commerce\DeliveryWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('deliveries.view-assigned') || $request->user()->can('deliveries.manage'), 403);
        $jobs = Delivery::query()
            ->with(['order.user:id,name', 'order.zone'])
            ->when(! $request->user()->can('deliveries.manage'), function ($query) use ($request): void {
                $query->where(function ($inner) use ($request): void {
                    $inner->where('agent_id', $request->user()->id)
                        ->orWhere(fn ($open) => $open->whereNull('agent_id')->where('status', 'pending'));
                });
            })
            ->latest()
            ->paginate(20);

        return view('pages.delivery.jobs', [
            'jobs' => $jobs,
            'balance' => (int) ($request->user()->wallet?->balance ?? 0),
        ]);
    }

    public function advance(Request $request, Delivery $delivery, DeliveryWorkflow $workflow): RedirectResponse
    {
        abort_unless($request->user()->can('deliveries.update') || $request->user()->can('deliveries.manage'), 403);
        $data = $request->validate([
            'status' => ['required', 'in:accepted,departed,en_route,arrived,delivered'],
            'eta_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ]);
        $workflow->advance($delivery, $request->user(), $data['status'], isset($data['eta_minutes']) ? (int) $data['eta_minutes'] : null);

        return back()->with('success', __('commerce.delivery_updated'));
    }
}
