<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoPrices\Contracts\SyncsPrices;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\MagentoPrice;

class SyncPrices implements SyncsPrices
{
    public function sync(int $retrieveLimit = null, int $updateLimit = null): void
    {
        $this->resetDoubleStatus();

        MagentoPrice::shouldRetrieve()
            ->select(['sku'])
            ->take($retrieveLimit ?? config('magento-prices.retrieve_limit'))
            ->get()
            ->each(fn (MagentoPrice $price) => RetrievePriceJob::dispatch($price->sku));

        MagentoPrice::shouldUpdate()
            ->take($updateLimit ?? config('magento-prices.update_limit'))
            ->get()
            ->each(fn (MagentoPrice $price) => UpdatePriceJob::dispatch($price->sku));
    }

    protected function resetDoubleStatus(): void
    {
        MagentoPrice::query()
            ->where('retrieve', '=', true)
            ->where('update', '=', true)
            ->update(['update' => false]);
    }

    public static function bind(): void
    {
        app()->singleton(SyncsPrices::class, static::class);
    }
}
