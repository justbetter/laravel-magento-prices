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
        if (config('magento-prices.async')) {
            $response = $this->magento->postAsync('products/base-prices', ['prices' => $priceData->getMagentoBasePrices()]);
        } else {
            $response = $this->magento->post('products/base-prices', ['prices' => $priceData->getMagentoBasePrices()]);
        }

        $response->throw();

        $model = $priceData->getModel();
        $model->update(['last_updated' => now()]);

        activity()
            ->performedOn($model)
            ->withProperties($response->json())
            ->log('Updated base price in Magento');
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMagentoBasePrice::class, static::class);
    }
}
