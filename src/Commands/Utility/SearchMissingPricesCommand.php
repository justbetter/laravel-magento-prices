<?php

namespace JustBetter\MagentoPrices\Commands\Utility;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\Utility\SyncMissingPricesJob;

class SearchMissingPricesCommand extends Command
{
    protected $signature = 'magento-prices:insert-missing-prices';

    protected $description = 'Dispatch job to search for missing prices in Magento';

    public function handle(): int
    {
        SyncMissingPricesJob::dispatch();

        return static::SUCCESS;
    }
}
