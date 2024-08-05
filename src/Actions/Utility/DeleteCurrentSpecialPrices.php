<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Utility\DeletesCurrentSpecialPrices;
use JustBetter\MagentoPrices\Models\Price;

class DeleteCurrentSpecialPrices implements DeletesCurrentSpecialPrices
{
    public function __construct(protected Magento $magento) {}

    public function delete(Price $price): void
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
        app()->singleton(DeletesCurrentSpecialPrices::class, static::class);
    }
}
