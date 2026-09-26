<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\ContactLink;
use App\Data\SmartOffer;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageSearchRequest;
use App\Http\Requests\NearbyRequest;
use App\Services\Catalog\VendorContacts;
use App\Services\Search\ImageSearchService;
use App\Services\Search\SimilarProductSearch;
use App\Support\Money;
use Illuminate\Http\JsonResponse;

class SmartSearchController extends Controller
{
    public function __construct(private VendorContacts $contacts) {}

    public function image(ImageSearchRequest $request, ImageSearchService $service, SimilarProductSearch $search): JsonResponse
    {
        $stored = $service->store($request->file('image'), $request->user());
        $lat = $request->filled('lat') ? (float) $request->input('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->input('lng') : null;
        $result = $search->search(
            $stored->insight(),
            $lat,
            $lng,
            $request->input('sort', 'match') ?: 'match',
            \App\Support\NearbyQuery::radius($request->input('radius')),
        );

        return response()->json([
            'data' => [
                'id' => $stored->uuid,
                'limited' => $stored->limited,
                'provider' => $stored->provider,
                'message' => $stored->limited ? __('ui.smart.limited') : null,
                'analysis' => [
                    'label' => $stored->label,
                    'category' => $stored->detected_category,
                    'brand' => $stored->detected_brand,
                    'text' => $stored->detected_text,
                    'attributes' => $stored->detected_attributes,
                ],
                'preview_url' => route('search.image.file', $stored),
                'offers' => $this->offers($result['offers']),
                'nearest' => $result['nearest'] ? $this->offer($result['nearest']) : null,
                'promotions' => $lat !== null && $lng !== null ? $this->offers($result['promotions']) : [],
            ],
        ], 201);
    }

    public function nearby(NearbyRequest $request, SimilarProductSearch $search): JsonResponse
    {
        $offers = $search->nearbyProducts(
            $request->latitude(),
            $request->longitude(),
            $request->radiusKm(),
            $request->sortBy(),
        );

        return response()->json([
            'data' => [
                'radius_km' => $request->radiusKm(),
                'offers' => $this->offers($offers),
                'nearest' => collect($offers)->first(fn (SmartOffer $offer) => $offer->nearest)
                    ? $this->offer(collect($offers)->first(fn (SmartOffer $offer) => $offer->nearest))
                    : null,
            ],
        ]);
    }

    public function promotions(NearbyRequest $request, SimilarProductSearch $search): JsonResponse
    {
        return response()->json([
            'data' => [
                'radius_km' => $request->radiusKm(),
                'offers' => $this->offers($search->promotions(
                    $request->latitude(),
                    $request->longitude(),
                    $request->radiusKm(),
                )),
            ],
        ]);
    }

    public function shops(NearbyRequest $request): JsonResponse
    {
        $distances = app(\App\Services\Geo\NearbyCatalog::class)->shopDistances(
            $request->latitude(),
            $request->longitude(),
            $request->radiusKm(),
        );
        $shops = \App\Models\Shop::query()
            ->with('vendor.socialLinks')
            ->whereIn('id', array_keys($distances) ?: [0])
            ->get()
            ->sortBy(fn ($shop) => $distances[$shop->id] ?? PHP_INT_MAX)
            ->values();

        return response()->json([
            'data' => $shops->map(function ($shop) use ($distances) {
                $meters = $distances[$shop->id] ?? null;

                return [
                    'name' => $shop->name,
                    'slug' => $shop->slug,
                    'url' => route('shops.show', $shop),
                    'distance_meters' => $meters === null ? null : (int) round($meters),
                    'distance_label' => \App\Services\Geo\GeoDistance::format($meters),
                    'latitude' => $shop->publish_location ? (float) $shop->latitude : null,
                    'longitude' => $shop->publish_location ? (float) $shop->longitude : null,
                    'address' => $shop->publish_address ? $shop->publicAddress() : null,
                    'contacts' => $this->contactPayload($this->contacts->forShop($shop)),
                ];
            })->all(),
        ]);
    }

    public function products(NearbyRequest $request, SimilarProductSearch $search): JsonResponse
    {
        return response()->json([
            'data' => [
                'radius_km' => $request->radiusKm(),
                'offers' => $this->offers($search->nearbyProducts(
                    $request->latitude(),
                    $request->longitude(),
                    $request->radiusKm(),
                    $request->sortBy(),
                )),
            ],
        ]);
    }

    /**
     * @param  array<int, SmartOffer>  $offers
     * @return array<int, array<string, mixed>>
     */
    private function offers(array $offers): array
    {
        return array_map(fn (SmartOffer $offer) => $this->offer($offer), $offers);
    }

    /**
     * @return array<string, mixed>
     */
    private function offer(SmartOffer $offer): array
    {
        $shop = $offer->product->shop;

        return [
            'match_percent' => $offer->matchPercent,
            'match_label' => __('ui.smart.match_kinds.'.$offer->matchKind),
            'nearest' => $offer->nearest,
            'stock' => $offer->stock,
            'stock_label' => $offer->stockLabel,
            'distance_meters' => $offer->distanceMeters,
            'distance_label' => $offer->distanceLabel,
            'price' => [
                'amount' => $offer->finalPrice,
                'formatted' => Money::format($offer->finalPrice, $offer->currency),
                'compare_amount' => $offer->comparePrice,
                'compare_formatted' => $offer->compareFormatted(),
                'currency' => $offer->currency,
                'discount_percent' => $offer->discountPercent,
                'savings' => $offer->savings,
                'savings_formatted' => $offer->savingsFormatted(),
                'ends_at' => $offer->promotionEndsAt,
            ],
            'criteria' => [
                'match_percent' => $offer->matchPercent,
                'distance' => $offer->distanceLabel,
                'stock' => $offer->stock,
                'price' => Money::format($offer->finalPrice, $offer->currency),
                'discount_percent' => $offer->discountPercent,
            ],
            'product' => [
                'name' => $offer->product->name,
                'slug' => $offer->product->slug,
                'url' => route('products.show', $offer->product),
            ],
            'shop' => [
                'name' => $shop->name,
                'slug' => $shop->slug,
                'url' => route('shops.show', $shop),
                'latitude' => $shop->publish_location && $shop->latitude !== null ? (float) $shop->latitude : null,
                'longitude' => $shop->publish_location && $shop->longitude !== null ? (float) $shop->longitude : null,
                'address' => $shop->publish_address ? $shop->publicAddress() : null,
            ],
            'contacts' => $this->contactPayload($this->contacts->forShop($shop, $offer->product->name)),
        ];
    }

    /**
     * @param  array<int, ContactLink>  $links
     * @return array<int, array<string, mixed>>
     */
    private function contactPayload(array $links): array
    {
        return array_map(fn (ContactLink $link) => [
            'platform' => $link->platform,
            'label' => $link->label,
            'href' => $link->href,
        ], $links);
    }
}
