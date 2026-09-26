<?php

namespace App\Services\Catalog;

use App\Enums\SocialPlatform;
use App\Http\Requests\Catalog\ShopRequest;
use App\Models\Shop;
use App\Models\VendorSocialLink;

class ShopProfileUpdater
{
    public function __construct(private MediaStorage $media) {}

    public function update(Shop $shop, ShopRequest $request, bool $allowStatus): Shop
    {
        $shop->fill([
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'location' => $request->input('location'),
            'country' => $request->input('country'),
            'province' => $request->input('province'),
            'city' => $request->input('city'),
            'commune' => $request->input('commune'),
            'quarter' => $request->input('quarter'),
            'address_line' => $request->input('address_line'),
            'latitude' => $request->filled('latitude') ? $request->input('latitude') : null,
            'longitude' => $request->filled('longitude') ? $request->input('longitude') : null,
            'opening_hours' => $request->input('opening_hours'),
            'publish_location' => $request->exists('publish_location')
                ? $request->boolean('publish_location')
                : ($shop->exists ? $shop->publish_location : true),
            'publish_address' => $request->exists('publish_address')
                ? $request->boolean('publish_address')
                : ($shop->exists ? $shop->publish_address : true),
        ]);

        if ($request->filled('slug')) {
            $shop->slug = $request->string('slug')->toString();
        }

        if ($allowStatus) {
            $shop->status = $request->string('status')->toString();
        }

        if ($request->hasFile('logo')) {
            $this->media->delete($shop->logo);
            $shop->logo = $this->media->store($request->file('logo'), 'shops/logos');
        }

        if ($request->hasFile('cover_image')) {
            $this->media->delete($shop->cover_image);
            $shop->cover_image = $this->media->store($request->file('cover_image'), 'shops/covers');
        }

        $shop->save();

        $vendor = $shop->vendor;

        if ($vendor) {
            if ($request->exists('business_name') || $request->exists('manager_name')) {
                $vendor->fill([
                    'business_name' => $request->input('business_name', $vendor->business_name),
                    'manager_name' => $request->input('manager_name', $vendor->manager_name),
                ])->save();
            }

            $this->syncSocials($vendor->id, $request);
        }

        return $shop;
    }

    private function syncSocials(int $vendorId, ShopRequest $request): void
    {
        foreach (SocialPlatform::formPlatforms() as $platform) {
            if (! $request->exists($platform->value)) {
                continue;
            }

            $value = trim((string) $request->input($platform->value));

            if ($value === '') {
                VendorSocialLink::query()
                    ->where('vendor_id', $vendorId)
                    ->where('platform', $platform->value)
                    ->delete();

                continue;
            }

            $isUrl = preg_match('#^https://#i', $value) === 1;

            VendorSocialLink::query()->updateOrCreate(
                ['vendor_id' => $vendorId, 'platform' => $platform->value],
                [
                    'username' => $isUrl ? null : ltrim($value, '@'),
                    'url' => $isUrl ? $value : null,
                ],
            );
        }
    }
}
