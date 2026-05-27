<?php

namespace App\Support;

use Illuminate\Support\Str;

class ServiceIcon
{
    private const URL_OVERRIDES = [
        'microsoft' => 'https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/microsoft.svg',
        'yahoo' => 'https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/yahoo.svg',
    ];

    private const SIMPLE_ICONS = [
        'airbnb' => 'airbnb',
        'amazon' => 'amazon',
        'aol' => 'aol',
        'apple' => 'apple',
        'binance' => 'binance',
        'bolt' => 'bolt',
        'discord' => 'discord',
        'facebook' => 'facebook',
        'google' => 'google',
        'gmail' => 'gmail',
        'grab' => 'grab',
        'instagram' => 'instagram',
        'kakaotalk' => 'kakaotalk',
        'line' => 'line',
        'linkedin' => 'linkedin',
        'microsoft' => 'microsoft',
        'naver' => 'naver',
        'netflix' => 'netflix',
        'paypal' => 'paypal',
        'signal' => 'signal',
        'skype' => 'skype',
        'snapchat' => 'snapchat',
        'steam' => 'steam',
        'telegram' => 'telegram',
        'tiktok' => 'tiktok',
        'tinder' => 'tinder',
        'twitter' => 'x',
        'uber' => 'uber',
        'viber' => 'viber',
        'wechat' => 'wechat',
        'whatsapp' => 'whatsapp',
        'x' => 'x',
        'yahoo' => 'yahoo',
        'youtube' => 'youtube',
    ];

    private const CODE_ALIASES = [
        'fb' => 'facebook',
        'go' => 'google',
        'ig' => 'instagram',
        'mm' => 'microsoft',
        'ot' => 'telegram',
        'tg' => 'telegram',
        'tw' => 'twitter',
        'wa' => 'whatsapp',
        'ya' => 'yahoo',
        'yt' => 'youtube',
    ];

    public static function url(string $name, ?string $code = null, ?string $customUrl = null): ?string
    {
        if (filled($customUrl)) {
            return $customUrl;
        }

        $slug = self::slug($name, $code);

        if (! $slug) {
            return null;
        }

        return self::URL_OVERRIDES[$slug] ?? "https://cdn.simpleicons.org/{$slug}";
    }

    public static function initials(string $name): string
    {
        $words = preg_split('/[^A-Za-z0-9]+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = collect($words)
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'OTP';
    }

    private static function slug(string $name, ?string $code = null): ?string
    {
        $normalizedCode = Str::lower((string) $code);
        if (isset(self::CODE_ALIASES[$normalizedCode])) {
            return self::SIMPLE_ICONS[self::CODE_ALIASES[$normalizedCode]] ?? null;
        }

        $normalizedName = Str::lower($name);

        foreach (self::SIMPLE_ICONS as $needle => $slug) {
            if (str_contains($normalizedName, $needle)) {
                return $slug;
            }
        }

        return null;
    }
}
