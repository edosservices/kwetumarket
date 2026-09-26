<?php

namespace App\Services\Catalog;

use App\Data\ContactLink;
use App\Enums\SocialPlatform;
use App\Models\Shop;
use App\Models\VendorSocialLink;
use Illuminate\Support\Collection;

class VendorContacts
{
    /**
     * @return array<int, ContactLink>
     */
    public function forShop(Shop $shop, ?string $productName = null): array
    {
        $shop->loadMissing('vendor.socialLinks');
        $links = [];
        $phone = $this->phone($shop->phone);

        if ($phone !== null) {
            $links[] = new ContactLink('phone', __('ui.smart.call'), 'tel:'.$phone, false);
        }

        /** @var Collection<int, VendorSocialLink> $socials */
        $socials = $shop->vendor?->socialLinks ?? collect();
        $whatsapp = $socials->first(fn (VendorSocialLink $link) => $link->platform === SocialPlatform::Whatsapp);
        $digits = $whatsapp ? $this->digits($whatsapp->username ?: $whatsapp->url) : null;

        if ($digits !== null) {
            $message = $productName
                ? __('ui.smart.whatsapp_message', ['product' => $productName])
                : __('ui.smart.whatsapp_message_shop', ['shop' => $shop->name]);
            $links[] = new ContactLink(
                'whatsapp',
                'WhatsApp',
                'https://wa.me/'.$digits.'?text='.rawurlencode($message),
                true,
            );
        }

        foreach ([SocialPlatform::Instagram, SocialPlatform::Tiktok, SocialPlatform::Facebook, SocialPlatform::Website] as $platform) {
            $row = $socials->first(fn (VendorSocialLink $link) => $link->platform === $platform);
            $href = $row ? $this->socialHref($platform, $row) : null;

            if ($href !== null) {
                $links[] = new ContactLink($platform->value, __('ui.smart.platforms.'.$platform->value), $href, true);
            }
        }

        $email = $this->email($shop->email);

        if ($email !== null) {
            $links[] = new ContactLink('email', __('ui.smart.platforms.email'), 'mailto:'.$email, false);
        }

        return $links;
    }

    public function phone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', trim($value)) ?? '';

        return preg_match('/^\+?[0-9]{8,15}$/', $normalized) ? $normalized : null;
    }

    public function digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return preg_match('/^[0-9]{8,15}$/', $digits) ? $digits : null;
    }

    private function email(?string $value): ?string
    {
        if ($value === null || ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $value;
    }

    private function socialHref(SocialPlatform $platform, VendorSocialLink $link): ?string
    {
        $url = $this->https($link->url);

        if ($url !== null) {
            return $url;
        }

        $username = ltrim(trim((string) $link->username), '@');

        if ($username === '' || ! preg_match('/^[A-Za-z0-9._]{1,80}$/', $username)) {
            return null;
        }

        return match ($platform) {
            SocialPlatform::Instagram => 'https://instagram.com/'.$username,
            SocialPlatform::Tiktok => 'https://www.tiktok.com/@'.$username,
            SocialPlatform::Facebook => 'https://facebook.com/'.$username,
            SocialPlatform::Website => null,
            default => null,
        };
    }

    private function https(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        return preg_match('#^https://#i', $url) ? $url : null;
    }
}
