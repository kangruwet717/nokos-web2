<?php

namespace App\Support;

use Illuminate\Support\Str;

class CountryFlag
{
    private const ISO_CODES = [
        'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ',
        'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS',
        'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN',
        'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE',
        'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF',
        'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM',
        'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM',
        'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC',
        'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK',
        'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA',
        'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG',
        'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW',
        'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS',
        'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO',
        'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI',
        'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA', 'ZM', 'ZW',
    ];

    private const ALIASES = [
        'antigua and barbuda' => 'AG',
        'argentina' => 'AR',
        'argentinas' => 'AR',
        'bolivia' => 'BO',
        'bosnia and herzegovina' => 'BA',
        'brunei' => 'BN',
        'brunei darussalam' => 'BN',
        'cape verde' => 'CV',
        'congo' => 'CG',
        'czech republic' => 'CZ',
        'democratic republic of the congo' => 'CD',
        'east timor' => 'TL',
        'hong kong' => 'HK',
        'iran' => 'IR',
        'ivory coast' => 'CI',
        'laos' => 'LA',
        'macau' => 'MO',
        'moldova' => 'MD',
        'myanmar' => 'MM',
        'north korea' => 'KP',
        'palestine' => 'PS',
        'russia' => 'RU',
        'south korea' => 'KR',
        'syria' => 'SY',
        'taiwan' => 'TW',
        'tanzania' => 'TZ',
        'turkey' => 'TR',
        'uk' => 'GB',
        'united kingdom' => 'GB',
        'usa' => 'US',
        'united states' => 'US',
        'venezuela' => 'VE',
        'vietnam' => 'VN',
    ];

    public static function isoCode(?string $countryName, ?string $providedIso = null): ?string
    {
        $providedIso = strtoupper((string) $providedIso);

        if (preg_match('/^[A-Z]{2}$/', $providedIso)) {
            return $providedIso;
        }

        if (blank($countryName)) {
            return null;
        }

        $normalized = self::normalize($countryName);

        if (isset(self::ALIASES[$normalized])) {
            return self::ALIASES[$normalized];
        }

        foreach (self::ISO_CODES as $isoCode) {
            if (self::normalize(\Locale::getDisplayRegion('und-'.$isoCode, 'en')) === $normalized) {
                return $isoCode;
            }
        }

        return null;
    }

    public static function emoji(?string $isoCode): ?string
    {
        $isoCode = strtoupper((string) $isoCode);

        if (! preg_match('/^[A-Z]{2}$/', $isoCode)) {
            return null;
        }

        return mb_chr(127397 + ord($isoCode[0]), 'UTF-8').mb_chr(127397 + ord($isoCode[1]), 'UTF-8');
    }

    private static function normalize(string $value): string
    {
        $value = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\(.+\)/', '')
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();

        return preg_replace('/^(the )/', '', $value) ?: '';
    }
}
