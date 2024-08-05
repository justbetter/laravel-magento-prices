<?php

namespace JustBetter\MagentoPrices\Commands\Retrieval;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;

class RetrievePriceCommand extends Command
{
    protected $signature = 'magento-prices:retrieve {sku}';

    protected $description = 'Retrieve price for a specific SKU';

    public function handle(): int
    {
        /** @var string $sku */
        $sku = $this->argument('sku');

        RetrievePriceJob::dispatch($sku);

        return static::SUCCESS;
    }
}
