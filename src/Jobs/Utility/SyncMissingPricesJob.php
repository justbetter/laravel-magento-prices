<?php

namespace JustBetter\MagentoPrices\Jobs\Utility;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\Utility\FindsProductsWithMissingPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class SyncMissingPricesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct()
    {
        $this->queue = config('magento-prices.queue');
    }

    public function handle(FindsProductsWithMissingPrices $findsProductsWithMissingPrices): void
    {
        $products = $findsProductsWithMissingPrices->retrieve();

        $pricesToUpdate = collect();

        foreach ($products as $sku) {
            /** @var Price $price */
            $price = Price::query()->firstWhere('sku', '=', $sku);

            if ($price !== null) {
                $pricesToUpdate[] = $price;

                continue;
            }

            RetrievePriceJob::dispatch($sku);
        }

        if (config('magento-prices.async')) {
            $repository = BaseRepository::resolve();

            $pricesToUpdate
                ->chunk($repository->updateLimit())
                ->each(fn (Collection $chunk): PendingDispatch => UpdatePricesAsyncJob::dispatch($chunk));
        } else {
            $pricesToUpdate->each(fn (Price $price): PendingDispatch => UpdatePriceJob::dispatch($price));
        }
    }
}
