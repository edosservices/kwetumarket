<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CatalogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\BrandRequest;
use App\Models\Brand;
use App\Services\Catalog\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Brand::class);

        return view('pages.admin.brands.index', [
            'brands' => Brand::query()->withCount('products')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Brand::class);

        return view('pages.admin.brands.form', [
            'brand' => new Brand(['status' => CatalogStatus::Active]),
        ]);
    }

    public function store(BrandRequest $request, MediaStorage $media): RedirectResponse
    {
        $brand = new Brand($this->attributes($request));

        if ($request->hasFile('logo')) {
            $brand->logo = $media->store($request->file('logo'), 'brands');
        }

        $brand->save();

        return redirect()->route('admin.brands.index')->with('status', __('ui.catalog.brand_saved'));
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('update', $brand);

        return view('pages.admin.brands.form', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand, MediaStorage $media): RedirectResponse
    {
        $brand->fill($this->attributes($request));

        if ($request->hasFile('logo')) {
            $media->delete($brand->logo);
            $brand->logo = $media->store($request->file('logo'), 'brands');
        }

        $brand->save();

        return redirect()->route('admin.brands.index')->with('status', __('ui.catalog.brand_saved'));
    }

    public function destroy(Brand $brand, MediaStorage $media): RedirectResponse
    {
        $this->authorize('delete', $brand);

        if ($brand->products()->exists()) {
            $brand->update(['status' => 'inactive']);

            return back()->with('status', __('ui.catalog.brand_in_use'));
        }

        $media->delete($brand->logo);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', __('ui.catalog.brand_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(BrandRequest $request): array
    {
        $data = $request->safe()->except(['logo', 'slug']);

        if ($request->filled('slug')) {
            $data['slug'] = $request->string('slug')->toString();
        }

        return $data;
    }
}
