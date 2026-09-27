<x-layouts.dashboard :title="__('operations.import_csv')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('operations.import_csv') }}</h1>
    <p class="mt-2 max-w-xl text-sm text-twende-muted">{{ __('operations.import_columns') }} {{ __('operations.import_csv_only') }}</p>
    <form method="POST" action="{{ route('vendor.import.store') }}" enctype="multipart/form-data" class="mt-6 grid max-w-xl gap-3">
        @csrf
        <x-select name="shop_id" :label="__('ui.catalog.shop')" :options="$shops->all()" />
        <label class="text-sm">CSV <input type="file" name="file" accept=".csv,text/csv" required class="mt-1 block text-sm"></label>
        <button class="h-11 w-fit rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('operations.import_csv') }}</button>
    </form>
</x-layouts.dashboard>
