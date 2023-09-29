<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\ImportGroupsJob;

class ImportGroupsCommand extends Command
{
    protected $signature = 'magento:price:import-groups';

    protected $description = 'Import all customer groups from Magento';

    public function handle(): int
    {
        ImportGroupsJob::dispatch();

        return static::SUCCESS;
    }
}
