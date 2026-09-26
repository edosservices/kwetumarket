<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $shops = Shop::query()
            ->where('status', ShopStatus::Active)
            ->withCount(['products' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->paginate(20);

        return ShopResource::collection($shops);
    }

    public function show(Shop $shop): ShopResource
    {
        abort_unless($shop->isPublic() || request()->user()?->can('view', $shop), 404);

        $shop->loadCount(['products' => fn ($query) => $query->published()]);

        return new ShopResource($shop);
    }
}
