<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\SyncMissingPricesJob;

class SearchMissingPricesCommand extends Command
{
    protected $signature = 'magento:price:missing';

    protected $description = 'Dispatch job to search for missing prices in Magento';

    public function handle(): int
    {
        $this->info('Dispatching...');

        SyncMissingPricesJob::dispatch();

        $this->info('Done!');

        return static::SUCCESS;
    }
}
