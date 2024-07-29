<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use JustBetter\MagentoPrices\Contracts\ProcessesPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class ProcessPrices implements ProcessesPrices
{
    public function process(): void
    {
        $repository = BaseRepository::resolve();

        Price::query()
            ->where('retrieve', '=', true)
            ->select(['sku'])
            ->take($repository->retrieveLimit())
            ->get()
            ->each(fn (Price $price): PendingDispatch => RetrievePriceJob::dispatch($price->sku));

        if (config('magento-prices.async')) {
            $prices = Price::query()
                ->where('update', '=', true)
                ->whereHas('product', function (Builder $query): void {
                    $query->where('exists_in_magento', '=', true);
                })
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get();

            UpdatePricesAsyncJob::dispatch($prices);
        } else {
            Price::query()
                ->where('update', '=', true)
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get()
                ->each(fn (Price $price): PendingDispatch => UpdatePriceJob::dispatch($price));
        }
    }

    public static function bind(): void
    {
        app()->singleton(ProcessesPrices::class, static::class);
    }
}
