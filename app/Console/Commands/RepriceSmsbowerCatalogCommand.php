<?php

namespace App\Console\Commands;

use App\Models\ServicePrice;
use App\Services\Pricing\SmsbowerPricingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepriceSmsbowerCatalogCommand extends Command
{
    protected $signature = 'smsbower:reprice-catalog
        {--chunk=1000 : Number of price rows processed per chunk}
        {--use-default-margin : Replace each row margin with current SMSBOWER_DEFAULT_MARGIN_* config before repricing}';

    protected $description = 'Recalculate SMSBower selling prices from stored provider prices and current pricing config';

    public function handle(SmsbowerPricingService $pricing): int
    {
        $chunk = max((int) $this->option('chunk'), 1);
        $useDefaultMargin = (bool) $this->option('use-default-margin');
        $defaultMarginType = (string) config('services.smsbower.default_margin_type', 'percent');
        $defaultMarginValue = (string) config('services.smsbower.default_margin_value', 30);
        $updated = 0;

        ServicePrice::query()
            ->whereHas('provider', fn ($query) => $query->where('code', 'smsbower'))
            ->orderBy('id')
            ->chunkById($chunk, function ($prices) use ($pricing, $useDefaultMargin, $defaultMarginType, $defaultMarginValue, &$updated): void {
                $now = now();
                $ids = [];
                $marginTypeCases = [];
                $marginValueCases = [];
                $sellingPriceCases = [];
                $marginTypeBindings = [];
                $marginValueBindings = [];
                $sellingPriceBindings = [];

                foreach ($prices as $price) {
                    $marginType = $useDefaultMargin ? $defaultMarginType : (string) $price->margin_type;
                    $marginValue = $useDefaultMargin ? $defaultMarginValue : (string) $price->margin_value;
                    $sellingPrice = $pricing->sellingPriceIdr(
                        (string) $price->provider_price,
                        $marginType,
                        $marginValue,
                    );

                    $ids[] = (int) $price->id;
                    $marginTypeCases[] = 'when ? then ?';
                    $marginValueCases[] = 'when ? then ?';
                    $sellingPriceCases[] = 'when ? then ?';
                    array_push($marginTypeBindings, (int) $price->id, $marginType);
                    array_push($marginValueBindings, (int) $price->id, $marginValue);
                    array_push($sellingPriceBindings, (int) $price->id, $sellingPrice);
                }

                if ($ids === []) {
                    return;
                }

                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $bindings = [
                    ...$marginTypeBindings,
                    ...$marginValueBindings,
                    ...$sellingPriceBindings,
                    $now,
                    ...$ids,
                ];

                DB::update(
                    'update service_prices set '.
                    'margin_type = case id '.implode(' ', $marginTypeCases).' end, '.
                    'margin_value = case id '.implode(' ', $marginValueCases).' end, '.
                    'selling_price = case id '.implode(' ', $sellingPriceCases).' end, '.
                    'updated_at = ? '.
                    "where id in ({$placeholders})",
                    $bindings,
                );

                $updated += count($ids);
            });

        $this->components->info("SMSBower repriced: {$updated} price rows updated.");

        return self::SUCCESS;
    }
}
