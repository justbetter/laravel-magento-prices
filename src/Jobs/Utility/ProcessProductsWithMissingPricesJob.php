<?php

namespace JustBetter\MagentoPrices\Jobs\Utility;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\MagentoPrices\Contracts\Utility\ProcessesProductsWithMissingPrices;

class ProcessProductsWithMissingPricesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct()
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(ProcessesProductsWithMissingPrices $contract): void
    {
        $contract->process();
    }
}
