<?php

namespace JustBetter\MagentoPrices\Commands\Update;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\Price;

class UpdateAllPricesCommand extends Command
{
    protected $signature = 'magento-prices:update-all';

    protected $description = 'Update all prices to Magento';

    public function handle(): int
    {
        Price::query()
            ->whereHas('product', function (Builder $query): void {
                $query->where('exists_in_magento', '=', true);
            })
            ->get()
            ->each(fn (Price $price): PendingDispatch => UpdatePriceJob::dispatch($price));

        return static::SUCCESS;
    }
}
