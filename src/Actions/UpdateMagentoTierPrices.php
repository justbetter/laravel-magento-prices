<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoTierPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Exceptions\PriceUpdateException;
use JustBetter\MagentoPrices\Jobs\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Models\MagentoCustomerGroup;

class UpdateMagentoTierPrices implements UpdatesMagentoTierPrice
{
    public function __construct(
        protected Magento $magento
    ) {
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
        cache()->remember('magento:prices:customer:groups:imported', now()->addDay(), function (): bool {
            ImportCustomerGroupsJob::dispatch();

            return true;
        });

        $groups = MagentoCustomerGroup::query()->pluck('code');

        if ($groups->isEmpty()) {
            throw new PriceUpdateException('The Magento customer groups are not imported');
        }

        return $groups
            ->push('ALL GROUPS')
            ->toArray();
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMagentoTierPrice::class, static::class);
    }
}
