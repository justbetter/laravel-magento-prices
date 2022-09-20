<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoBasePrice;
use JustBetter\MagentoPrices\Data\PriceData;

class UpdateMagentoBasePrice implements UpdatesMagentoBasePrice
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(PriceData $priceData): void
    {
        $response = $this->magento
            ->post('products/base-prices', ['prices' => $priceData->getMagentoBasePrices()])
            ->throw()
            ->json();

        $model = $priceData->getModel();
        $model->update(['last_updated' => now()]);

        activity()
            ->performedOn($model)
            ->withProperties($response)
            ->log('Updated base price in Magento');
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMagentoBasePrice::class, static::class);
    }
}
