<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoTierPrice;
use JustBetter\MagentoPrices\Data\PriceData;

class UpdateMagentoTierPrices implements UpdatesMagentoTierPrice
{
    public function __construct(protected Magento $magento)
    {
    }

    public function update(PriceData $priceData): void
    {
        $response = $this->magento
            ->put('products/tier-prices', ['prices' => $priceData->getMagentoTierPrices()])
            ->throw()
            ->json();

        $model = $priceData->getModel();
        $model->update(['last_updated' => now()]);

        activity()
            ->performedOn($model)
            ->withProperties($response)
            ->log('Updated tier price in Magento');
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMagentoTierPrice::class, static::class);
    }
}
