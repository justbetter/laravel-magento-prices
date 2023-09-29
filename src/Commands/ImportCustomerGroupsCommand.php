<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\ImportCustomerGroupsJob;

class ImportCustomerGroupsCommand extends Command
{
    protected $signature = 'magento:price:import-customer-groups';

    protected $description = 'Import all customer groups from Magento';

    public function handle(): int
    {
        ImportCustomerGroupsJob::dispatch();

        return static::SUCCESS;
    }
}
