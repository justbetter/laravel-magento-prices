<?php

namespace JustBetter\MagentoPrices\Jobs\Retrieval;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesAllPrices;

class RetrieveAllPricesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public ?Carbon $from = null,
        public bool $defer = true,
    )
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(RetrievesAllPrices $prices): void
    {
        $prices->retrieve($this->from, $this->defer);
    }
}
