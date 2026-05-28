<?php

namespace App\Support;

use Illuminate\Support\Str;

class ServiceIcon
{
    private const URL_OVERRIDES = [
        'microsoft' => 'https://cdn.simpleicons.org/microsoft',
        'yahoo' => 'https://cdn.simpleicons.org/yahoo',
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
        'reddit' => 'reddit',
        'docusign' => 'docusign',
        'adobe' => 'adobe',
        'swagbucks' => 'swagbucks',
        'digitalocean' => 'digitalocean',
        'pokemon' => 'pokemon',
        'kick' => 'kick',
        'nexon' => 'nexon',
        'coursera' => 'coursera',
        'tumblr' => 'tumblr',
        'audible' => 'audible',
        'rapidapi' => 'rapidapi',
        'zillow' => 'zillow',
        'seatgeek' => 'seatgeek',
        'github' => 'github',
        'copilot' => 'microsoftcopilot',
        'chatgpt' => 'openai',
        'claude' => 'anthropic',
        'gemini' => 'google',
        'aws' => 'amazonaws',
        'wellsfargo' => 'wellsfargo',
        'spotify' => 'spotify',
        'pinterest' => 'pinterest',
        'zoom' => 'zoom',
        'slack' => 'slack',
        'twitch' => 'twitch',
        'vimeo' => 'vimeo',
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
        'aws' => 'aws',
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

        // 1. If it's a known popular Simple Icon, return the Simple Icons CDN URL
        if (self::isKnownSimpleIcon($slug)) {
            return self::URL_OVERRIDES[$slug] ?? "https://cdn.simpleicons.org/{$slug}";
        }

        // 2. Otherwise, try Clearbit Logo API by guessing the domain name
        $domain = self::guessDomain($name, $slug);
        if ($domain) {
            return "https://logo.clearbit.com/{$domain}";
        }

        // 3. Fallback to Simple Icons
        return "https://cdn.simpleicons.org/{$slug}";
    }

    private static function isKnownSimpleIcon(string $slug): bool
    {
        return in_array($slug, self::SIMPLE_ICONS) || isset(self::URL_OVERRIDES[$slug]);
    }

    private static function guessDomain(string $name, ?string $slug): ?string
    {
        $lowerName = Str::lower(trim($name));
        if (preg_match('/^[a-z0-9\-]+\.[a-z]{2,6}$/', $lowerName)) {
            return $lowerName;
        }

        if (! $slug) {
            return null;
        }

        // Curated domain mapping for specific niche/regional brands
        $domainMap = [
            'shopsy' => 'shopsy.in',
            'tokopedia' => 'tokopedia.com',
            'shopee' => 'shopee.co.id',
            'gojek' => 'gojek.com',
            'grab' => 'grab.com',
            'dana' => 'dana.id',
            'ovo' => 'ovo.id',
            'linkaja' => 'linkaja.id',
            'lazada' => 'lazada.co.id',
            'blibli' => 'blibli.com',
            'bukalapak' => 'bukalapak.com',
            'ruangguru' => 'ruangguru.com',
            'kopi-kenangan' => 'kopikenangan.com',
            'gopay' => 'gopay.co.id',
            'indomaret' => 'indomaret.co.id',
            'alfamart' => 'alfamart.co.id',
            'pelago' => 'pelago.co',
            'fmcpay' => 'fmcpay.com',
            'huione-pay' => 'huione.com',
            'rummyyes' => 'rummyyes.com',
            'wallapop' => 'wallapop.com',
            'ludo11' => 'ludo11.com',
            'truemeds' => 'truemeds.in',
            'nykaa' => 'nykaa.com',
            'paisabazaar' => 'paisabazaar.com',
            'cred' => 'cred.club',
            'kick' => 'kick.com',
            'poe' => 'poe.com',
            'whatnot' => 'whatnot.com',
            'exness' => 'exness.com',
            'tata-motors' => 'tatamotors.com',
            'timewall' => 'timewall.io',
            'omnicard' => 'omnicard.in',
            'credit-karma' => 'creditkarma.com',
            '3fun' => '3fun.app',
            'outlier' => 'outlier.ai',
            'atlas-earth' => 'atlasearth.com',
            'cursor' => 'cursor.com',
            'quoka' => 'quoka.de',
            'bigbasket' => 'bigbasket.com',
            'woohoo' => 'woohoo.in',
            'first-games' => 'firstgames.in',
            'kickcash' => 'kickcash.in',
            'aadhar' => 'uidai.gov.in',
            'shriram-one' => 'shriramone.in',
            'konvy' => 'konvy.com',
            'milanuncios' => 'milanuncios.com',
            'clubgg' => 'clubgg.net',
            'polloai' => 'pollo.ai',
            'toluna' => 'toluna.com',
            'foundit' => 'foundit.in',
            'big-cash' => 'bigcash.live',
            'fdj-parions-sport' => 'parionssport.fdj.fr',
            'enilive' => 'enilive.it',
            'beutea' => 'beutea.com.my',
            'tutti' => 'tutti.ch',
            'kredito' => 'kredito.id',
            'idee-opinioni' => 'ideeopinioni.it',
            'guvi' => 'guvi.in',
            'kolotibablo' => 'kolotibablo.com',
            'mackolik' => 'mackolik.com',
            'long-chau' => 'nhathuoclongchau.com.vn',
            'wamba' => 'wamba.com',
            'dahadaha' => 'dahadaha.com',
            'guzman-y-gomez' => 'guzmanygomez.com',
            'ricardo' => 'ricardo.ch',
            'ourtime' => 'ourtime.com',
            't-online' => 't-online.de',
            'thedermaco' => 'thedermaco.com',
            'brevistay' => 'brevistay.com',
            'australia-post' => 'auspost.com.au',
            'cheq' => 'cheq.one',
            'betflag' => 'betflag.it',
            'sisal' => 'sisal.it',
            'lottomatica' => 'lottomatica.it',
            'goldbet' => 'goldbet.it',
            'snai' => 'snai.it',
            'eurobet' => 'eurobet.it',
            'hungry-panda' => 'hungrypanda.co',
            'rusdate' => 'rusdate.net',
            'ownly' => 'ownly.io',
            'black-forest-labs' => 'blackforestlabs.ai',
            'gaintplay' => 'gaintplay.com',
            'rupiyo' => 'rupiyo.in',
            'raja-games' => 'rajagames.com',
        ];

        if (isset($domainMap[$slug])) {
            return $domainMap[$slug];
        }

        // Clean up slug to make a standard domain
        return "{$slug}.com";
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
            return self::SIMPLE_ICONS[self::CODE_ALIASES[$normalizedCode]] ?? self::CODE_ALIASES[$normalizedCode];
        }

        $normalizedName = Str::lower($name);

        // 1. Try to find defined simple icons by needle match
        foreach (self::SIMPLE_ICONS as $needle => $slug) {
            if (str_contains($normalizedName, $needle)) {
                return $slug;
            }
        }

        // 2. Dynamic slugification as fallback
        // Remove content in brackets or parentheses: e.g. "RedNote / Xiaohongshu" -> we'll split it
        $cleaned = preg_replace('/\(.*?\)/', '', $normalizedName);
        $cleaned = preg_replace('/\[.*?\]/', '', $cleaned);

        // Split by slashes, pipes or other separators
        $parts = preg_split('/[\/|]+/', $cleaned);
        $partToUse = trim($parts[0]);

        // If the first part contains non-ASCII characters (e.g. Chinese characters) and the second part is ASCII, use the second part
        if (count($parts) > 1) {
            $part0 = trim($parts[0]);
            $part1 = trim($parts[1]);
            if (preg_match('/[^\x00-\x7F]/', $part0) && ! preg_match('/[^\x00-\x7F]/', $part1)) {
                $partToUse = $part1;
            }
        }

        // Keep only alphanumeric characters, spaces, and hyphens
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', Str::lower($partToUse));

        // Convert consecutive spaces or hyphens to a single hyphen
        $slug = preg_replace('/[\s\-]+/', '-', trim($slug));

        if (empty($slug)) {
            return null;
        }

        return $slug;
    }
}
