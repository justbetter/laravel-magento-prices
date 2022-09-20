<?php

namespace JustBetter\MagentoPrices\Jobs;

use DateTime;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Contracts\RetrievesSkus;

class RetrievePricesJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected ?Carbon $from = null,
        protected bool $forceUpdate = false
    ) {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(): void
    {
        /** @var RetrievesSkus $retriever */
        $retriever = app(config('magento-prices.retrievers.sku'));

        $prices = $this->hasFromDate()
            ? $retriever->retrieveByDate($this->from)
            : $retriever->retrieveAll();

        $prices->each(fn (string $sku) => RetrievePriceJob::dispatch($sku));
    }

    protected function hasFromDate(): bool
    {
        return $this->from !== null && is_a($this->from, DateTime::class);
    }

    public function tags(): array
    {
        return [
            'force:'.($this->forceUpdate ? 'true' : 'false'),
            $this->hasFromDate() ? 'from:'.$this->from->toDateTimeString() : 'all',
        ];
    }
}
