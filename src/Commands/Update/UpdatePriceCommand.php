<?php

namespace JustBetter\MagentoPrices\Commands\Update;

use Illuminate\Console\Command;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\Price;

class UpdatePriceCommand extends Command
{
    protected $signature = 'magento-prices:update {sku}';

    protected $description = 'Dispatch job to update a price in Magento';

    public function handle(): int
    {
        /** @var string $sku */
        $sku = $this->argument('sku');

        /** @var Price $price */
        $price = Price::query()
            ->where('sku', '=', $sku)
            ->firstOrFail();

        UpdatePriceJob::dispatch($price);

        return static::SUCCESS;
    }
}
