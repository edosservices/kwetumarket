<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\VendorStatusRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = Vendor::query()
            ->with(['user:id,name,email', 'shops:id,vendor_id,name,slug,status'])
            ->withCount('shops')
            ->latest()
            ->paginate(20);

        $missing = User::role(UserRole::Vendor->value)
            ->whereDoesntHave('vendorProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('pages.admin.vendors.index', [
            'vendors' => $vendors,
            'missing' => $missing,
        ]);
    }

    public function update(VendorStatusRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('update', $vendor);
        $vendor->update(['status' => VendorStatus::from((string) $request->input('status'))]);

        return back()->with('status', __('ui.catalog.vendor_saved'));
    }

    public function store(VendorStatusRequest $request): RedirectResponse
    {
        $this->authorize('create', Vendor::class);

        $user = User::query()->findOrFail($request->integer('user_id'));
        abort_unless($user->hasRole(UserRole::Vendor->value), 422);

        Vendor::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['status' => VendorStatus::from((string) $request->input('status'))],
        );

        return back()->with('status', __('ui.catalog.vendor_saved'));
    }
}
