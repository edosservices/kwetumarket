<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\CatalogImporter;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CatalogImportController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()->vendorProfile, 403);

        return view('pages.vendor.commerce.import', [
            'shops' => $request->user()->vendorProfile->shops()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request, CatalogImporter $importer): RedirectResponse
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $data = $request->validate([
            'shop_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:2048'],
        ]);
        $shop = Shop::query()->where('vendor_id', $vendor->id)->whereKey($data['shop_id'])->first();
        abort_unless($shop, 403);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());

        if ($extension !== 'csv') {
            throw ValidationException::withMessages(['file' => __('operations.import_csv_only')]);
        }

        $result = $importer->import($shop, $request->file('file')->getContent(), $request->user());
        $message = __('operations.import_done', ['count' => $result['created']]);

        if ($result['errors'] !== []) {
            $message .= ' '.implode(' ', array_slice($result['errors'], 0, 3));
        }

        return back()->with($result['created'] > 0 ? 'success' : 'error', $message);
    }
}
