<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\ProcessPricesJob;

class ProcessPricesCommand extends Command
{
    protected $signature = 'magento-prices:process';

    protected $description = 'Process prices that have the retrieve and update flags set';

    public function handle(): int
    {
        ProcessPricesJob::dispatch();

        return static::SUCCESS;
    }
}
