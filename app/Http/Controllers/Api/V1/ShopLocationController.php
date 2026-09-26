<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopLocationRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;

class ShopLocationController extends Controller
{
    public function update(ShopLocationRequest $request, Shop $shop): ShopResource
    {
        $shop->update([
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);

        return new ShopResource($shop);
    }
}
