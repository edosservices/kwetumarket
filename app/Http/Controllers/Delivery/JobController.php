<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\CourierProfile;
use App\Models\Delivery;
use App\Services\Catalog\MediaStorage;
use App\Services\Commerce\DeliveryWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('deliveries.view-assigned') || $request->user()->can('deliveries.manage'), 403);
        $profile = $request->user()->courierProfile;
        $open = ! $profile || $profile->canAcceptJobs() || $request->user()->can('deliveries.manage');
        $jobs = Delivery::query()
            ->with(['order.user:id,name', 'order.zone'])
            ->when(! $request->user()->can('deliveries.manage'), function ($query) use ($request, $open): void {
                $query->where(function ($inner) use ($request, $open): void {
                    $inner->where('agent_id', $request->user()->id);

                    if ($open) {
                        $inner->orWhere(fn ($available) => $available->whereNull('agent_id')->where('status', 'pending'));
                    }
                });
            })
            ->latest()
            ->paginate(20);

        return view('pages.delivery.jobs', [
            'jobs' => $jobs,
            'balance' => (int) ($request->user()->wallet?->balance ?? 0),
            'profile' => $profile,
        ]);
    }

    public function profile(Request $request, MediaStorage $media): RedirectResponse
    {
        abort_unless($request->user()->hasRole('delivery_agent') || $request->user()->hasRole('admin'), 403);
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'vehicle_type' => ['nullable', 'string', 'max:40'],
            'vehicle_plate' => ['nullable', 'string', 'max:32'],
            'availability' => ['required', 'in:offline,available,busy,on_delivery,paused'],
            'document' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        if (! empty($data['phone'])) {
            $request->user()->update(['phone' => $data['phone']]);
        }

        $profile = CourierProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['availability' => CourierProfile::OFFLINE],
        );
        $profile->fill([
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'vehicle_plate' => $data['vehicle_plate'] ?? null,
            'availability' => $data['availability'],
        ]);

        if ($request->hasFile('document')) {
            $profile->document_disk = $media->disk();
            $profile->document_path = $media->store($request->file('document'), 'couriers/'.$request->user()->id);
        }

        $profile->save();

        return back()->with('success', __('operations.courier_saved'));
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
