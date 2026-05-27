<?php

namespace App\Services\Pricing;

class SmsbowerPricingService
{
    public function providerCostIdr(string $providerPrice): string
    {
        return number_format((float) bcmul($providerPrice, $this->usdToIdrRate(), 4), 2, '.', '');
    }

    public function sellingPriceIdr(string $providerPrice, string $marginType, string $marginValue): string
    {
        $basePrice = $this->providerCostIdr($providerPrice);

        $withMargin = $marginType === 'fixed'
            ? bcadd($basePrice, $marginValue, 4)
            : bcadd($basePrice, bcmul($basePrice, bcdiv($marginValue, '100', 4), 4), 4);

        $minimumProfitPrice = bcadd($basePrice, $this->minimumProfit(), 4);
        if (bccomp($withMargin, $minimumProfitPrice, 2) < 0) {
            $withMargin = $minimumProfitPrice;
        }

        $minimum = $this->minimumPrice();
        if (bccomp($withMargin, $minimum, 2) < 0) {
            $withMargin = $minimum;
        }

        return number_format($this->roundUp((float) $withMargin, $this->rounding()), 2, '.', '');
    }

    private function usdToIdrRate(): string
    {
        return (string) config('services.smsbower.usd_to_idr_rate', 16000);
    }

    private function minimumPrice(): string
    {
        return (string) config('services.smsbower.minimum_selling_price_idr', 1000);
    }

    private function minimumProfit(): string
    {
        return (string) config('services.smsbower.minimum_profit_idr', 0);
    }

    private function rounding(): int
    {
        return max((int) config('services.smsbower.rounding_idr', 100), 1);
    }

    private function roundUp(float $amount, int $rounding): float
    {
        return ceil($amount / $rounding) * $rounding;
    }
}
