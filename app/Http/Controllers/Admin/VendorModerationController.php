<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorModerationController extends Controller
{
    public function approve(Request $request, Vendor $vendor): RedirectResponse
    {
        abort_unless($request->user()->can('vendors.approve'), 403);

        $vendor->forceFill(['status' => 'active'])->save();

        AuditLog::record($request->user(), 'vendor.approve', $vendor, [
            'module' => 'vendors',
        ]);

        return back();
    }

    public function suspend(Request $request, Vendor $vendor): RedirectResponse
    {
        abort_unless($request->user()->can('vendors.suspend'), 403);

        $vendor->forceFill(['status' => 'suspended'])->save();

        AuditLog::record($request->user(), 'vendor.suspend', $vendor, [
            'module' => 'vendors',
        ]);

        return back();
    }
}
