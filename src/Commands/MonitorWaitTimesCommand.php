<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\MonitorWaitTimesJob;

class MonitorWaitTimesCommand extends Command
{
    protected $signature = 'magento:price:monitor-wait-times';

    protected $description = 'Dispatch job to monitor the wait times';

    public function handle(): int
    {
        $this->info('Dispatching...');

        MonitorWaitTimesJob::dispatch();

        $this->info('Done!');

        return static::SUCCESS;
    }
}
