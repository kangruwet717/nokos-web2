<?php

namespace Tests\Unit;

use App\Support\ServiceIcon;
use Tests\TestCase;

class ServiceIconTest extends TestCase
{
    public function test_resolves_common_service_icon_from_code_or_name(): void
    {
        $this->assertSame('https://example.test/icon.png', ServiceIcon::url('Unknown', 'xx', 'https://example.test/icon.png'));
        $this->assertSame('https://cdn.simpleicons.org/whatsapp', ServiceIcon::url('Any name', 'wa'));
        $this->assertSame('https://cdn.simpleicons.org/google', ServiceIcon::url('Google, Gmail, Youtube', 'go'));
        $this->assertSame('https://cdn.simpleicons.org/telegram', ServiceIcon::url('Telegram', null));
        $this->assertSame('https://cdn.simpleicons.org/microsoft', ServiceIcon::url('Microsoft', 'mm'));
        $this->assertSame('https://cdn.simpleicons.org/yahoo', ServiceIcon::url('Yahoo', 'ya'));
    }

    public function test_returns_initials_for_unknown_service_names(): void
    {
        $this->assertSame('https://logo.clearbit.com/custom-otp-service.com', ServiceIcon::url('Custom OTP Service', 'custom'));
        $this->assertNull(ServiceIcon::url('!!!', 'custom'));
        $this->assertSame('CO', ServiceIcon::initials('Custom OTP Service'));
    }
}
