<?php

namespace JustBetter\MagentoPrices\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesSpecialPrice;
use JustBetter\MagentoPrices\Contracts\Utility\DeletesCurrentSpecialPrices;
use JustBetter\MagentoPrices\Models\Price;

class UpdateSpecialPrice implements UpdatesSpecialPrice
{
    public function __construct(
        protected Magento $magento,
        protected DeletesCurrentSpecialPrices $currentSpecialPrices
    ) {}

    public function update(Price $price): bool
    {
        $payload = collect($price->special_prices)
            ->map(fn (array $specialPrice): array => array_merge($specialPrice, [
                'sku' => $price->sku,
            ]));

        if ($price->has_special) {
            $this->currentSpecialPrices->delete($price);
        }

        $price->update([
            'has_special' => $payload->isNotEmpty(),
        ]);

        if ($payload->isEmpty()) {
            return true;
        }

        $response = $this->magento
            ->post('products/special-price', ['prices' => $payload])
            ->onError(function (Response $response) use ($price, $payload): void {
                activity()
                    ->on($price)
                    ->useLog('error')
                    ->withProperties([
                        'response' => $response->body(),
                        'payload' => $payload,
                    ])
                    ->log('Failed to update special price');
            });

        return $response->successful();
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesSpecialPrice::class, static::class);
    }
}
