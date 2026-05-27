<?php

namespace Tests\Unit;

use App\Support\CountryFlag;
use PHPUnit\Framework\TestCase;

class CountryFlagTest extends TestCase
{
    public function test_resolves_iso_code_from_common_provider_country_names(): void
    {
        $this->assertSame('ID', CountryFlag::isoCode('Indonesia'));
        $this->assertSame('US', CountryFlag::isoCode('USA'));
        $this->assertSame('GB', CountryFlag::isoCode('UK'));
        $this->assertSame('CI', CountryFlag::isoCode('Ivory Coast'));
        $this->assertSame('AR', CountryFlag::isoCode('Argentinas'));
    }

    public function test_respects_provider_iso_code_when_available(): void
    {
        $this->assertSame('MY', CountryFlag::isoCode('Malaysia', 'my'));
    }

    public function test_returns_null_for_unknown_country_names(): void
    {
        $this->assertNull(CountryFlag::isoCode('Unknown Provider Region'));
        $this->assertNull(CountryFlag::emoji(null));
    }
}
