<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageSearchRequest;
use App\Models\ImageSearch;
use App\Services\Search\ImageSearchService;
use App\Services\Search\SimilarProductSearch;
use App\Support\NearbyQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageSearchController extends Controller
{
    public function store(ImageSearchRequest $request, ImageSearchService $service): RedirectResponse
    {
        $search = $service->store($request->file('image'), $request->user());

        return redirect()->route('search.image.show', array_filter([
            'imageSearch' => $search,
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'radius' => NearbyQuery::radius($request->input('radius')),
            'sort' => NearbyQuery::sort($request->input('sort'), 'match'),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function show(Request $request, ImageSearch $imageSearch, SimilarProductSearch $search): View
    {
        abort_if($imageSearch->isExpired(), 404);

        $lat = NearbyQuery::coordinate($request->query('lat'), -90, 90);
        $lng = NearbyQuery::coordinate($request->query('lng'), -180, 180);
        $radius = NearbyQuery::radius($request->query('radius'));
        $sort = NearbyQuery::sort($request->query('sort'), 'match');
        $result = $search->search($imageSearch->insight(), $lat, $lng, $sort, $radius);

        return view('pages.smart.image', [
            'search' => $imageSearch,
            'insight' => $imageSearch->insight(),
            'offers' => $result['offers'],
            'nearest' => $result['nearest'],
            'promotions' => $lat !== null && $lng !== null ? $result['promotions'] : [],
            'sort' => $sort,
            'radius' => $radius,
            'located' => $lat !== null && $lng !== null,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    public function file(ImageSearch $imageSearch): StreamedResponse
    {
        abort_if($imageSearch->isExpired(), 404);

        $disk = Storage::disk($imageSearch->disk);
        abort_unless($disk->exists($imageSearch->image_path), 404);

        return $disk->response($imageSearch->image_path, null, [
            'Content-Type' => $disk->mimeType($imageSearch->image_path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
