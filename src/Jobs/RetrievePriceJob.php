<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoPrices\Contracts\RetrievesPrice;

class RetrievePriceJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $sku,
        protected bool $forceUpdate = false
    ) {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(): void
    {
        /** @var RetrievesPrice $retriever */
        $retriever = app(config('magento-prices.retrievers.price'));

        $price = $retriever->retrieve($this->sku);

        if ($price === null) {
            return;
        }

        ProcessPriceJob::dispatch($price, $this->forceUpdate);
    }

    public function tags(): array
    {
        return [
            $this->sku,
            'force:'.($this->forceUpdate ? 'true' : 'false'),
        ];
    }
}
