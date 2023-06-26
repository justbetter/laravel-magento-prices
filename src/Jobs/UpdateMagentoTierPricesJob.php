<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoTierPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use Throwable;

class UpdateMagentoTierPricesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(protected PriceData $price)
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(UpdatesMagentoTierPrice $updatesMagentoTierPrice): void
    {
        $updatesMagentoTierPrice->update($this->price);
    }

    public function failed(Throwable $exception): void
    {
        if (is_a($exception, RequestException::class)) {
            $response = $exception->response->body();
        }

        activity()
            ->on($this->price->getModel())
            ->withProperties([
                'priceData' => $this->price->getMagentoBasePrices(),
                'message' => $exception->getMessage(),
                'response' => $response ?? '',
                'metadata' => [
                    'level' => 'error',
                ],
            ])
            ->log('Failed to update tier prices');

        $this->price->getModel()->registerError();
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
