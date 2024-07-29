<?php

namespace JustBetter\MagentoPrices\Jobs\Retrieval;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\MagentoPrices\Contracts\Retrieval\SavesPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Models\Price;
use Spatie\Activitylog\ActivityLogger;
use Throwable;

class SavePriceJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public PriceData $data,
        public bool $forceUpdate
    ) {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(SavesPrice $price): void
    {
        $price->save($this->data, $this->forceUpdate);
    }

    public function uniqueId(): string
    {
        return $this->data['sku'];
    }

    public function tags(): array
    {
        return [
            $this->data['sku'],
        ];
    }

    /** @codeCoverageIgnore */
    public function failed(Throwable $exception): void
    {
        /** @var ?Price $model */
        $model = Price::query()->firstWhere('sku', '=', $this->data['sku']);

        activity()
            ->when($model, function (ActivityLogger $logger, Price $price): ActivityLogger {
                return $logger->on($price);
            })
            ->useLog('error')
            ->log('Failed to save price: '.$exception->getMessage());
    }
}
