<?php

namespace JustBetter\MagentoPrices\Commands\Utility;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;

class ImportCustomerGroupsCommand extends Command
{
    protected $signature = 'magento-prices:import-customer-groups';

    protected $description = 'Import all customer groups from Magento';

    public function handle(): int
    {
        ImportCustomerGroupsJob::dispatch();

        return static::SUCCESS;
    }
}
