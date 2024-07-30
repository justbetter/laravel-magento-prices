<?php

namespace JustBetter\MagentoPrices\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesTierPricesAsync;
use JustBetter\MagentoPrices\Contracts\Utility\RetrievesCustomerGroups;
use JustBetter\MagentoPrices\Models\Price;

class UpdateTierPricesAsync implements UpdatesTierPricesAsync
{
    public function __construct(
        protected MagentoAsync $magentoAsync,
        protected RetrievesCustomerGroups $customerGroups
    ) {
    }

    public function update(Collection $prices): void
    {
        $prices = $prices->reject(fn (Price $price): bool => count($price->tier_prices) === 0);

        if ($prices->isEmpty()) {
            return;
        }

        $customerGroups = $this->customerGroups->retrieve();

        $payload = $prices
            ->map(function (Price $price) use($customerGroups): array {
                return [
                    'prices' => collect($price->tier_prices)
                        ->whereIn('customer_group', $customerGroups)
                        ->map(fn (array $tierPrice): array => array_merge($tierPrice, [
                            'sku' => $price->sku,
                        ]))
                        ->toArray()
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
