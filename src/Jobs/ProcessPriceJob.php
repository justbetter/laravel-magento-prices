<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoPrices\Contracts\ProcessesPrice;
use JustBetter\MagentoPrices\Data\PriceData;

class ProcessPriceJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected PriceData $price, protected bool $forceUpdate = false)
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(ProcessesPrice $processesPrice): void
    {
        $processesPrice->process($this->price, $this->forceUpdate);
    }

    public function uniqueId(): string
    {
        return $this->price->sku;
    }

    public function tags(): array
    {
        return [
            $this->price->sku,
        ];
    }
}
