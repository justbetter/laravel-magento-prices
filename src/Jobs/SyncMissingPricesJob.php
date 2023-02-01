<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoPrices\Contracts\FindsProductsWithMissingPrices;
use JustBetter\MagentoPrices\Models\MagentoPrice;

class SyncMissingPricesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;

    public function __construct()
    {
        $this->queue = config('magento-prices.queue');
    }

    public function handle(FindsProductsWithMissingPrices $findsProductsWithMissingPrices): void
    {
        $products = $findsProductsWithMissingPrices->retrieve();

        foreach ($products as $sku) {
            $price = MagentoPrice::findBySku($sku);

            if ($price !== null) {
                UpdatePriceJob::dispatch($sku);

                continue;
            }

            RetrievePriceJob::dispatch($sku);
        }
    }
}
