<?php

namespace JustBetter\MagentoPrices\Commands\Utility;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\Utility\ProcessProductsWithMissingPricesJob;

class ProcessProductsWithMissingPricesCommand extends Command
{
    protected $signature = 'magento-prices:process-missing-prices';

    protected $description = 'Update prices that are missing in Magneto if they are available';

    public function handle(): int
    {
        ProcessProductsWithMissingPricesJob::dispatch();

        return static::SUCCESS;
    }
}
