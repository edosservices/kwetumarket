<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CatalogStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->where('status', CatalogStatus::Active)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->where('status', CatalogStatus::Active)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        abort_unless(
            $category->status === CatalogStatus::Active || request()->user()?->can('categories.manage'),
            404,
        );

        $category->load(['children' => fn ($query) => $query->where('status', CatalogStatus::Active), 'parent']);

        return new CategoryResource($category);
    }
}
