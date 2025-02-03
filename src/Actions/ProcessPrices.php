<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\ProcessesPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class ProcessPrices implements ProcessesPrices
{
    public function __construct(protected Magento $magento) {}

    public function process(): void
    {
        $repository = BaseRepository::resolve();

        Price::query()
            ->where('sync', '=', true)
            ->where('retrieve', '=', true)
            ->select(['sku'])
            ->take($repository->retrieveLimit())
            ->get()
            ->each(fn (Price $price): PendingDispatch => RetrievePriceJob::dispatch($price->sku));

        if (! $this->magento->available()) {
            return;
        }

        if (config('magento-prices.async')) {
            $prices = Price::query()
                ->where('sync', '=', true)
                ->where('update', '=', true)
                ->whereHas('product', function (Builder $query): void {
                    $query->where('exists_in_magento', '=', true);
                })
                ->whereDoesntHave('bulkOperations', function (Builder $query): void {
                    $query
                        ->where('status', '=', OperationStatus::Open)
                        ->orWhereNull('status');
                })
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get();

            UpdatePricesAsyncJob::dispatchIf($prices->isNotEmpty(), $prices);
        } else {
            Price::query()
                ->where('sync', '=', true)
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
