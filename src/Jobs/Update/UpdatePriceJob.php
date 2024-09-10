<?php

namespace JustBetter\MagentoPrices\Jobs\Update;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoClient\Jobs\Middleware\AvailableMiddleware;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesPrice;
use JustBetter\MagentoPrices\Models\Price;

class UpdatePriceJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Price $price)
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(UpdatesPrice $contract): void
    {
        $contract->update($this->price);
    }

    public function uniqueId(): int
    {
        return $this->price->id;
    }

    public function tags(): array
    {
        return [
            $this->price->sku,
        ];
    }

    public function middleware(): array
    {
        return [
            new AvailableMiddleware,
        ];
    }
}
