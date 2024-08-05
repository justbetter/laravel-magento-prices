<?php

namespace JustBetter\MagentoPrices\Jobs\Retrieval;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesPrice;
use JustBetter\MagentoPrices\Models\Price;
use Spatie\Activitylog\ActivityLogger;
use Throwable;

class RetrievePriceJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public string $sku,
        public bool $forceUpdate = false
    ) {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(RetrievesPrice $price): void
    {
        $price->retrieve($this->sku, $this->forceUpdate);
    }

    public function uniqueId(): string
    {
        return $this->sku;
    }

    public function tags(): array
    {
        return [
            $this->sku,
        ];
    }

    /** @codeCoverageIgnore */
    public function failed(Throwable $exception): void
    {
        /** @var ?Price $model */
        $model = Price::query()->firstWhere('sku', '=', $this->sku);

        activity()
            ->when($model, function (ActivityLogger $logger, Price $price): ActivityLogger {
                return $logger->on($price);
            })
            ->useLog('error')
            ->log('Failed to retrieve price: '.$exception->getMessage());
    }
}
