<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\CatalogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CatalogSearchRequest;
use App\Models\Category;
use App\Services\Search\EloquentProductSearch;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', CatalogStatus::Active)
            ->with(['children' => fn ($query) => $query->where('status', CatalogStatus::Active)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.catalog.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(CatalogSearchRequest $request, Category $category, EloquentProductSearch $search): View
    {
        $user = auth()->user();

        if ($category->status !== CatalogStatus::Active && ($user === null || $user->cannot('view', $category))) {
            abort($user ? 403 : 404);
        }

        $category->load(['parent', 'children' => fn ($query) => $query->where('status', CatalogStatus::Active)]);

        $filters = $request->filters();
        $filters['category'] = $category->id;

        return view('pages.catalog.categories.show', [
            'category' => $category,
            'query' => $request->term(),
            'results' => $search->search($request->term(), $filters),
            'filters' => $filters,
        ]);
    }
}
