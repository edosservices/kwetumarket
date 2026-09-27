<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Notifications\AccountNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('pages.admin.products.edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->fill([
            'name' => $data['name'],
            'price_minor' => $data['price_minor'],
            'stock' => $data['stock'],
        ])->save();

        AuditLog::record($request->user(), 'product.update', $product, [
            'module' => 'products',
            'vendor_id' => $product->vendor_id,
        ]);

        return redirect()->route('admin.products.edit', $product);
    }

    public function approve(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('products.approve') && $request->user()->isPlatformStaff(), 403);

        if ($product->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $product->forceFill(['status' => 'published'])->save();

        AuditLog::record($request->user(), 'product.approve', $product, [
            'module' => 'products',
        ]);

        $product->loadMissing('vendor.owner');
        $product->vendor?->owner?->notify(new AccountNotice(
            __('ui.notifications.product_title'),
            __('ui.notifications.product_body', ['name' => $product->name]),
        ));

        return back();
    }
}
