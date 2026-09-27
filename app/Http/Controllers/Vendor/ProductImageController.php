<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Catalog\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductImageController extends Controller
{
    public function store(ProductImageRequest $request, Product $product, MediaStorage $media): RedirectResponse
    {
        $limit = max(1, (int) config('twende.media.max_images', 12));

        if ($product->images()->count() >= $limit) {
            throw ValidationException::withMessages([
                'image' => __('operations.image_limit', ['max' => $limit]),
            ]);
        }

        $path = $media->store($request->file('image'), 'products/'.$product->id);
        $original = $request->hasFile('original')
            ? $media->store($request->file('original'), 'products/'.$product->id.'/originals')
            : null;
        $isFirst = ! $product->images()->exists();

        $product->images()->create([
            'disk' => $media->disk(),
            'path' => $path,
            'original_path' => $original,
            'thumb_path' => $media->thumbnail($path),
            'alt_text' => $request->input('alt_text') ?: $product->name,
            'is_primary' => $isFirst,
            'sort_order' => (int) $product->images()->max('sort_order') + 1,
        ]);

        return back()->with('status', __('ui.catalog.image_saved'));
    }

    public function primary(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);
        abort_unless((int) $image->product_id === (int) $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('status', __('ui.catalog.image_saved'));
    }

    public function move(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);
        abort_unless((int) $image->product_id === (int) $product->id, 404);
        $direction = $request->string('direction')->toString();
        $neighbor = $product->images()
            ->where('sort_order', $direction === 'up' ? '<' : '>', $image->sort_order)
            ->orderBy('sort_order', $direction === 'up' ? 'desc' : 'asc')
            ->first();

        if ($neighbor) {
            $current = $image->sort_order;
            $image->update(['sort_order' => $neighbor->sort_order]);
            $neighbor->update(['sort_order' => $current]);
        }

        return back()->with('status', __('ui.catalog.image_saved'));
    }

    public function destroy(Request $request, Product $product, ProductImage $image, MediaStorage $media): RedirectResponse
    {
        $this->authorize('update', $product);
        abort_unless((int) $image->product_id === (int) $product->id, 404);

        $wasPrimary = $image->is_primary;
        $media->delete($image->path, $image->disk);
        $media->delete($image->original_path, $image->disk);
        $media->delete($image->thumb_path, $image->disk);
        $image->delete();

        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('status', __('ui.catalog.image_deleted'));
    }
}
