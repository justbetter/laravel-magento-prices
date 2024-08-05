<?php

namespace JustBetter\MagentoPrices\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesBasePricesAsync;
use JustBetter\MagentoPrices\Models\Price;

class UpdateBasePricesAsync implements UpdatesBasePricesAsync
{
    public function __construct(protected MagentoAsync $magentoAsync) {}

    public function update(Collection $prices): void
    {
        $prices = $prices->reject(fn (Price $price): bool => count($price->base_prices ?? []) === 0);

        if ($prices->isEmpty()) {
            return;
        }

        $payload = $prices
            ->map(function (Price $price): array {
                return [
                    'prices' => collect($price->base_prices)
                        ->map(fn (array $basePrice): array => array_merge($basePrice, [
                            'sku' => $price->sku,
                        ]))
                        ->toArray(),
                ];
            })
            ->toArray();

        $this->magentoAsync
            ->subjects($prices->all())
            ->postBulk('products/base-prices', $payload);
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesBasePricesAsync::class, static::class);
    }
}
