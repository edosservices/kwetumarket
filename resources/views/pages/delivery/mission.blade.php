<x-layouts.delivery :title="__('ui.modules.missions')">
    <h1 class="text-2xl font-bold">{{ $delivery->order?->number }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ $delivery->status }}</p>
    @can('update', $delivery)
        <form method="POST" action="{{ route('delivery.missions.update', $delivery) }}" class="mt-6 max-w-xl space-y-4">
            @csrf
            @method('PUT')
            <x-select
                name="status"
                :label="__('ui.fields.status')"
                :selected="$delivery->status"
                :options="[
                    'accepted' => __('ui.delivery.accepted'),
                    'declined' => __('ui.delivery.declined'),
                    'picked_up' => __('ui.delivery.picked_up'),
                    'in_transit' => __('ui.delivery.in_transit'),
                    'delivered' => __('ui.delivery.delivered'),
                    'failed' => __('ui.delivery.failed'),
                ]"
            />
            <x-input name="proof_note" :label="__('ui.fields.proof')" :value="$delivery->proof_note" />
            <x-input name="latitude" :label="__('ui.fields.latitude')" :value="$delivery->latitude" />
            <x-input name="longitude" :label="__('ui.fields.longitude')" :value="$delivery->longitude" />
            <x-button type="submit">{{ __('ui.actions.save') }}</x-button>
        </form>
    @endcan
    @can('assign', $delivery)
        <form method="POST" action="{{ route('delivery.missions.assign', $delivery) }}" class="mt-8 max-w-xl space-y-4">
            @csrf
            <x-select
                name="agent_id"
                :label="__('ui.fields.agent')"
                :options="$agents->mapWithKeys(fn ($agent) => [$agent->id => $agent->name])->all()"
                :selected="$delivery->agent_id"
            />
            <x-button type="submit" variant="secondary">{{ __('ui.actions.assign') }}</x-button>
        </form>
    @endcan
</x-layouts.delivery>
