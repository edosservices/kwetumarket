<?php

namespace App\Http\Controllers;

use App\Services\Search\SimilarProductSearch;
use App\Support\NearbyQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NearbyController extends Controller
{
    public function __invoke(Request $request, SimilarProductSearch $search): View
    {
        $lat = NearbyQuery::coordinate($request->query('lat'), -90, 90);
        $lng = NearbyQuery::coordinate($request->query('lng'), -180, 180);
        $radius = NearbyQuery::radius($request->query('radius'));
        $sort = NearbyQuery::sort($request->query('sort'), 'nearest');
        $located = $lat !== null && $lng !== null;

        return view('pages.smart.nearby', [
            'offers' => $located ? $search->nearbyProducts($lat, $lng, $radius, $sort) : [],
            'sort' => $sort,
            'radius' => $radius,
            'located' => $located,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }
}
