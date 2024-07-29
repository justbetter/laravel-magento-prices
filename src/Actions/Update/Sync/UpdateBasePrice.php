<?php

namespace JustBetter\MagentoPrices\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesBasePrice;
use JustBetter\MagentoPrices\Models\Price;

class UpdateBasePrice implements UpdatesBasePrice
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(Price $price): bool
    {
        $payload = collect($price->base_prices)
            ->map(fn (array $basePrice): array => array_merge($basePrice, [
                'sku' => $price->sku,
            ]));

        if ($payload->isEmpty()) {
            return true;
        }

        $response = $this->magento
            ->post('products/base-prices', ['prices' => $payload])
            ->onError(function (Response $response) use ($price, $payload): void {
                activity()
                    ->on($price)
                    ->useLog('error')
                    ->withProperties([
                        'response' => $response->body(),
                        'payload' => $payload,
                    ])
                    ->log('Failed to update base price');
            });

        return $response->successful();
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesBasePrice::class, static::class);
    }
}
