<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CatalogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CategoryRequest;
use App\Models\Category;
use App\Services\Catalog\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->with('parent:id,name')
            ->withCount(['children', 'products'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        return view('pages.admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('pages.admin.categories.form', [
            'category' => new Category(['status' => CatalogStatus::Active, 'sort_order' => 0]),
            'parents' => Category::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(CategoryRequest $request, MediaStorage $media): RedirectResponse
    {
        $category = new Category($this->attributes($request));

        if ($request->hasFile('image')) {
            $category->image = $media->store($request->file('image'), 'categories');
        }

        $category->save();

        return redirect()->route('admin.categories.index')->with('status', __('ui.catalog.category_saved'));
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('pages.admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()->whereKeyNot($category->id)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(CategoryRequest $request, Category $category, MediaStorage $media): RedirectResponse
    {
        $category->fill($this->attributes($request));

        if ($request->hasFile('image')) {
            $media->delete($category->image);
            $category->image = $media->store($request->file('image'), 'categories');
        }

        $category->save();

        return redirect()->route('admin.categories.index')->with('status', __('ui.catalog.category_saved'));
    }

    public function destroy(Category $category, MediaStorage $media): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->children()->exists() || $category->products()->exists()) {
            $category->update(['status' => 'inactive']);

            return back()->with('status', __('ui.catalog.category_in_use'));
        }

        $media->delete($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', __('ui.catalog.category_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(CategoryRequest $request): array
    {
        $data = $request->safe()->except(['image', 'slug']);

        if ($request->filled('slug')) {
            $data['slug'] = $request->string('slug')->toString();
        }

        return $data;
    }
}
