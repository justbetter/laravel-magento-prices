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
        $existingGroups = $this->getGroups();

        $tierPrices = collect($priceData->getMagentoTierPrices())
            ->whereIn('customer_group', $existingGroups);

        if (config('magento-prices.async')) {
            $response = $this->magento->putAsync('products/tier-prices', ['prices' => $tierPrices->toArray()]);
        } else {
            $response = $this->magento->put('products/tier-prices', ['prices' => $tierPrices->toArray()]);
        }

        $response->throw();

        $model = $priceData->getModel();
        $model->update(['last_updated' => now()]);

        activity()
            ->performedOn($model)
            ->withProperties($response->json())
            ->log('Updated tier price in Magento');
    }

    protected function getGroups(): array
    {
        return cache()->remember(
            'magento:prices:customer:groups',
            now()->addDay(),
            fn () => $this->magento
                ->lazy('customerGroups/search')
                ->collect()
                ->pluck('code')
                ->toArray()
        );
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMagentoTierPrice::class, static::class);
    }
}
