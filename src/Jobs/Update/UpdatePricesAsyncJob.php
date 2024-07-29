<?php

namespace JustBetter\MagentoPrices\Jobs\Update;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesPricesAsync;

class UpdatePricesAsyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Collection $prices)
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(UpdatesPricesAsync $contract): void
    {
        $contract->update($this->prices);
    }

    public function tags(): array
    {
        return $this->prices->pluck('sku')->toArray();
    }
}
