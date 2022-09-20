<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Support\Enumerable;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoSpecialPrice;
use JustBetter\MagentoPrices\Data\PriceData;

class UpdateMagentoSpecialPrices implements UpdatesMagentoSpecialPrice
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(PriceData $priceData): void
    {
        $this->deleteCurrentSpecialPrices($priceData->sku);

        $response = $this->magento
            ->post('products/special-price', ['prices' => $priceData->getMagentoSpecialPrices()])
            ->throw()
            ->json();

        $model = $priceData->getModel();
        $model->update(['last_updated' => now()]);

        activity()
            ->performedOn($model)
            ->withProperties($response)
            ->log('Updated special price in Magento');
    }

    protected function deleteCurrentSpecialPrices(string $sku): void
    {
        $this->magento
            ->post('products/special-price-information', ['skus' => [$sku]])
            ->throw()
            ->collect()
            ->chunk(20)
            ->each(function (Enumerable $enumerable): void {
                $this->magento
                    ->post('products/special-price-delete', ['prices' => $enumerable->toArray()])
                    ->throw();
            });
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMagentoSpecialPrice::class, static::class);
    }
}
