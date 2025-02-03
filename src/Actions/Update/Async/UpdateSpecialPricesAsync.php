<?php

namespace JustBetter\MagentoPrices\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoAsync\Client\MagentoAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesSpecialPricesAsync;
use JustBetter\MagentoPrices\Contracts\Utility\DeletesCurrentSpecialPrices;
use JustBetter\MagentoPrices\Models\Price;

class UpdateSpecialPricesAsync implements UpdatesSpecialPricesAsync
{
    public function __construct(
        protected MagentoAsync $magentoAsync,
        protected DeletesCurrentSpecialPrices $currentSpecialPrices
    ) {}

    public function update(Collection $prices): void
    {
        $prices->where('has_special', '=', true)
            ->chunk(250)
            ->each(fn (Collection $prices) => $this->currentSpecialPrices->delete($prices->pluck('sku')->toArray()));

        $prices->each(fn (Price $price) => $price->update([
            'has_special' => count($price->special_prices ?? []) > 0,
        ]));

        $prices = $prices
            ->reject(fn (Price $price): bool => count($price->special_prices ?? []) === 0)
            ->values();

        if ($prices->isEmpty()) {
            return;
        }

        $payload = $prices
            ->map(function (Price $price): array {
                return [
                    'prices' => collect($price->special_prices)
                        ->map(fn (array $tierPrice): array => array_merge($tierPrice, [
                            'sku' => $price->sku,
                        ]))
                        ->toArray(),
                ];
            })
            ->toArray();

        $this->magentoAsync
            ->subjects($prices->all())
            ->postBulk('products/special-price', $payload);
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesSpecialPricesAsync::class, static::class);
    }
}
