<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\RetrievePricesJob;

class RetrievePricesCommand extends Command
{
    protected $signature = 'magento:price:retrieve {sku?} {--date=}';

    protected $description = 'Dispatch job to retrieve price(s)';

    public function handle(): int
    {
        $this->info('Dispatching...');

        if ($this->argument('sku') !== null) {
            RetrievePriceJob::dispatch($this->argument('sku'));
        } else {
            RetrievePricesJob::dispatch($this->option('date') !== null ? Carbon::parse($this->option('date')) : null);
        }

        $this->info('Done!');

        return static::SUCCESS;
    }
}
