<?php

namespace JustBetter\MagentoPrices\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesTierPricesAsync;
use JustBetter\MagentoPrices\Contracts\Utility\FiltersTierPrices;
use JustBetter\MagentoPrices\Models\Price;

class UpdateTierPricesAsync implements UpdatesTierPricesAsync
{
    public function __construct(
        protected MagentoAsync $magentoAsync,
        protected FiltersTierPrices $filterTierPrice,
    ) {}

    public function update(Collection $prices): void
    {
        $prices = $prices->reject(fn (Price $price): bool => count($price->tier_prices ?? []) === 0)->values();

        if ($prices->isEmpty()) {
            return;
        }

        $payload = $prices
            ->map(function (Price $price): array {
                $filteredTierPrices = $this->filterTierPrice->filter($price->sku, $price->tier_prices ?? []);

                return [
                    'prices' => collect($filteredTierPrices)
                        ->map(fn (array $tierPrice): array => array_merge($tierPrice, [
                            'sku' => $price->sku,
                        ]))
                        ->toArray(),
                ];
            })
            ->toArray();

        $this->magentoAsync
            ->subjects($prices->all())
            ->putBulk('products/tier-prices', $payload);
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesTierPricesAsync::class, static::class);
    }
}
