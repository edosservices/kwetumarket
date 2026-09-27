<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('addresses.manage'), 403);

        return view('pages.commerce.addresses', [
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('addresses.manage'), 403);
        $data = $this->validated($request);
        $data['is_default'] = ! $request->user()->addresses()->exists() || $request->boolean('is_default');

        if ($data['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $request->user()->addresses()->create($data);

        return back()->with('success', __('operations.address_saved'));
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->owns($request, $address);
        $data = $this->validated($request);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address->update($data);

        return back()->with('success', __('operations.address_saved'));
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->owns($request, $address);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', __('operations.address_deleted'));
    }

    public function primary(Request $request, Address $address): RedirectResponse
    {
        $this->owns($request, $address);
        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', __('operations.address_saved'));
    }

    private function owns(Request $request, Address $address): void
    {
        abort_unless($request->user()->can('addresses.manage'), 403);
        abort_unless((int) $address->user_id === (int) $request->user()->id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:40'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'country' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
            'commune' => ['nullable', 'string', 'max:80'],
            'quarter' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        $data['phone'] = PhoneNumber::normalize($data['phone']);

        return $data;
    }
}
