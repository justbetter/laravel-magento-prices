<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Commands\Retrieval;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrieveAllPricesJob;

class RetrieveAllPricesCommand extends Command
{
    protected $signature = 'magento-prices:retrieve-all {from?} {--queue}';

    protected $description = 'Retrieve all prices, optionally filtered by date';

    public function handle(): int
    {
        /** @var ?string $from */
        $from = $this->argument('from');

        $defer = ! $this->option('queue');

        $carbon = blank($from) ? null : Carbon::parse($from);

        RetrieveAllPricesJob::dispatch($carbon, $defer);

        return static::SUCCESS;
    }
}
