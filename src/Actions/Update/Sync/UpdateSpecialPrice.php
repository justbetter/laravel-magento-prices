<?php

namespace JustBetter\MagentoPrices\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesSpecialPrice;
use JustBetter\MagentoPrices\Models\Price;

class UpdateSpecialPrice implements UpdatesSpecialPrice
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(Price $price): bool
    {
        $payload = collect($price->special_prices)
            ->map(fn (array $specialPrice): array => array_merge($specialPrice, [
                'sku' => $price->sku,
            ]));

        if ($price->has_special) {
            $this->deleteCurrentSpecialPrices($price);
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

    protected function deleteCurrentSpecialPrices(Price $price): void
    {
        $this->magento
            ->post('products/special-price-information', ['skus' => [$price->sku]])
            ->throw()
            ->collect()
            ->chunk(20)
            ->each(function (Enumerable $specialPrices) use ($price): void {
                $this->magento
                    ->post('products/special-price-delete', ['prices' => $specialPrices->toArray()])
                    ->onError(function (Response $response) use ($price, $specialPrices): void {
                        activity()
                            ->on($price)
                            ->useLog('error')
                            ->withProperties([
                                'response' => $response->body(),
                                'payload' => $specialPrices->toArray(),
                            ])
                            ->log('Failed to remove special price');
                    })
                    ->throw();
            });
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesSpecialPrice::class, static::class);
    }
}
