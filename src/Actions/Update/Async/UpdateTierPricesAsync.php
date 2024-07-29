<?php

namespace JustBetter\MagentoPrices\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoPrices\Actions\Utility\RetrieveCustomerGroups;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesTierPricesAsync;
use JustBetter\MagentoPrices\Models\Price;

class UpdateTierPricesAsync implements UpdatesTierPricesAsync
{
    public function __construct(
        protected MagentoAsync $magentoAsync,
        protected RetrieveCustomerGroups $customerGroups
    ) {
    }

    public function update(Collection $prices): void
    {
        $prices = $prices->reject(fn (Price $price): bool => count($price->tier_prices) === 0 || ! $price->has_tier);

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
            ->postBulk('products/tier-prices', $payload);
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesTierPricesAsync::class, static::class);
    }
}
