<?php

namespace JustBetter\MagentoPrices\Commands\Retrieval;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrieveAllPricesJob;

class RetrieveAllPricesCommand extends Command
{
    protected $signature = 'magento-prices:retrieve-all {from?}';

    protected $description = 'Retrieve all prices, optionally filtered by date';

    public function handle(): int
    {
        /** @var ?string $from */
        $from = $this->argument('from');

        $carbon = blank($from) ? null : Carbon::parse($from);

        RetrieveAllPricesJob::dispatch($carbon);

        return static::SUCCESS;
    }
}
