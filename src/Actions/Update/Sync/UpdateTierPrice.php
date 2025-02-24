<?php

namespace JustBetter\MagentoPrices\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesTierPrice;
use JustBetter\MagentoPrices\Contracts\Utility\FiltersTierPrices;
use JustBetter\MagentoPrices\Models\Price;

class UpdateTierPrice implements UpdatesTierPrice
{
    public function __construct(
        protected Magento $magento,
        protected FiltersTierPrices $filterTierPrice,
    ) {}

    public function update(Price $price): bool
    {
        $filteredTierPrices = $this->filterTierPrice->filter($price->sku, $price->tier_prices ?? []);

        $payload = collect($filteredTierPrices)->map(fn (array $tierPrice): array => array_merge($tierPrice, [
            'sku' => $price->sku,
        ]));

        if ($payload->isEmpty()) {
            return true;
        }

        $response = $this->magento
            ->put('products/tier-prices', ['prices' => $payload])
            ->onError(function (Response $response) use ($price, $payload): void {
                activity()
                    ->on($price)
                    ->useLog('error')
                    ->withProperties([
                        'response' => $response->body(),
                        'payload' => $payload,
                    ])
                    ->log('Failed to update tier price');
            });

        return $response->successful();
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesTierPrice::class, static::class);
    }
}
