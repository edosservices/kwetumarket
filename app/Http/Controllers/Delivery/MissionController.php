<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\User;
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
            'agents' => $request->user()->can('delivery.assign')
                ? User::role('delivery_agent')->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('update', $delivery);

        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'declined', 'picked_up', 'in_transit', 'delivered', 'failed'])],
            'proof_note' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $delivery->fill([
            'status' => $data['status'],
            'proof_note' => $data['proof_note'] ?? $delivery->proof_note,
            'latitude' => $data['latitude'] ?? $delivery->latitude,
            'longitude' => $data['longitude'] ?? $delivery->longitude,
        ])->save();

        AuditLog::record($request->user(), 'delivery.update', $delivery, [
            'status' => $delivery->status,
        ]);

        return redirect()->route('delivery.missions.show', $delivery);
    }

    public function assign(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('assign', $delivery);

        $data = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $agent = User::query()->findOrFail($data['agent_id']);
        abort_unless($agent->hasRole('delivery_agent'), 422);

        $delivery->forceFill([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ])->save();

        AuditLog::record($request->user(), 'delivery.assign', $delivery, [
            'agent_id' => $agent->id,
        ]);

        return redirect()->route('delivery.missions.show', $delivery);
    }
}
