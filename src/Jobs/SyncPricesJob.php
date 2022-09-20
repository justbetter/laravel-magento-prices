<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoPrices\Contracts\SyncsPrices;

class SyncPricesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ?int $retrieveLimit = null, protected ?int $updateLimit = null)
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(SyncsPrices $syncsPrices): void
    {
        $syncsPrices->sync($this->retrieveLimit, $this->updateLimit);
    }
}
