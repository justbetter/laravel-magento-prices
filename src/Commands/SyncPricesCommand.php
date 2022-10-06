<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\SyncPricesJob as SyncPricesJob;

class SyncPricesCommand extends Command
{
    protected $signature = 'magento:price:sync {limit?} {--sync}';

    protected $description = 'Dispatch job to sync price(s)';

    public function handle(): int
    {
        if ($this->option('sync')) {
            /** @phpstan-ignore-next-line */
            SyncPricesJob::dispatchSync($this->argument('limit'), $this->argument('limit'));
        } else {
            /** @phpstan-ignore-next-line */
            SyncPricesJob::dispatch($this->argument('limit'), $this->argument('limit'));
        }

        return static::SUCCESS;
    }
}
