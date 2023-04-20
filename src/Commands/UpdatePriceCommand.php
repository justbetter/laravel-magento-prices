<?php

namespace JustBetter\MagentoPrices\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\UpdatePriceJob;

class UpdatePriceCommand extends Command
{
    protected $signature = 'magento:price:update {sku} {--force}';

    protected $description = 'Dispatch job to update price(s) in Magento';

    public function handle(): int
    {
        $this->info('Dispatching...');

        /** @var string $sku */
        $sku = $this->argument('sku');

        /** @var bool $force */
        $force = $this->argument('force');

        UpdatePriceJob::dispatch($sku, $force);

        $this->info('Done!');

        return static::SUCCESS;
    }
}
