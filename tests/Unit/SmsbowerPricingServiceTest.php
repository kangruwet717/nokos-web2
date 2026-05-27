<?php

namespace Tests\Unit;

use App\Services\Pricing\SmsbowerPricingService;
use Tests\TestCase;

class SmsbowerPricingServiceTest extends TestCase
{
    public function test_selling_price_respects_minimum_profit_and_rounding(): void
    {
        config()->set('services.smsbower.usd_to_idr_rate', 17000);
        config()->set('services.smsbower.minimum_selling_price_idr', 500);
        config()->set('services.smsbower.minimum_profit_idr', 300);
        config()->set('services.smsbower.rounding_idr', 50);

        $price = (new SmsbowerPricingService)->sellingPriceIdr('0.0130', 'percent', '10');

        $this->assertSame('550.00', $price);
    }
}
