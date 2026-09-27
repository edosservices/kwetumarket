<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\CourierProfile;
use App\Models\Delivery;
use App\Models\User;
use App\Services\Workflow\DeliveryTransition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('delivery.view'), 403);

        $deliveries = Delivery::query()
            ->with('order')
            ->when(
                $request->user()->can('delivery.manage') || $request->user()->isPlatformStaff(),
                fn ($query) => $query,
                fn ($query) => $query->where('agent_id', $request->user()->id),
            )
            ->latest('id')
            ->paginate(12);

        return view('pages.delivery.missions', [
            'deliveries' => $deliveries,
        ]);
    }

    public function show(Request $request, Delivery $delivery): View
    {
        $this->authorize('view', $delivery);

        return view('pages.delivery.mission', [
            'delivery' => $delivery->load('order', 'agent'),
            'next' => DeliveryTransition::allowed($delivery->status),
            'agents' => $request->user()->can('delivery.assign')
                ? User::role('delivery_agent')->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request, Delivery $delivery, DeliveryTransition $workflow): RedirectResponse
    {
        $this->authorize('update', $delivery);

        $data = $request->validate([
            'status' => ['nullable', Rule::in(DeliveryTransition::allowed($delivery->status))],
            'eta_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'proof_note' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (! empty($data['status'])) {
            $workflow->advance(
                $delivery,
                $request->user(),
                $data['status'],
                isset($data['eta_minutes']) ? (int) $data['eta_minutes'] : null,
            );
            $delivery->refresh();
        }

        $delivery->fill([
            'proof_note' => $data['proof_note'] ?? $delivery->proof_note,
            'latitude' => $data['latitude'] ?? $delivery->latitude,
            'longitude' => $data['longitude'] ?? $delivery->longitude,
        ])->save();

        return redirect()->route('delivery.missions.show', $delivery);
    }

    public function assign(Request $request, Delivery $delivery, DeliveryTransition $workflow): RedirectResponse
    {
        $this->authorize('assign', $delivery);

        $data = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $agent = User::query()->findOrFail($data['agent_id']);
        abort_unless($agent->hasRole('delivery_agent'), 422);

        $workflow->assign($delivery, $request->user(), $agent);

        return redirect()->route('delivery.missions.show', $delivery);
    }

    public function profile(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('delivery_agent') || $request->user()->can('delivery.manage'), 403);

        $data = $request->validate([
            'vehicle_type' => ['nullable', 'string', 'max:40'],
            'vehicle_plate' => ['nullable', 'string', 'max:32'],
            'availability' => ['required', 'in:offline,available,busy,on_delivery,paused'],
        ]);

        $profile = CourierProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['availability' => CourierProfile::OFFLINE],
        );
        $profile->fill($data)->save();

        return back();
    }
}
