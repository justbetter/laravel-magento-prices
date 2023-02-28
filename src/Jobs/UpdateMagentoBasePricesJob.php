<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoBasePrice;
use JustBetter\MagentoPrices\Data\PriceData;
use Throwable;

class UpdateMagentoBasePricesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(protected PriceData $price)
    {
        $this->onQueue(config('magento-prices.queue'));
    }

    public function handle(UpdatesMagentoBasePrice $magentoBasePrice): void
    {
        $magentoBasePrice->update($this->price);
    }

    public function failed(Throwable $exception): void
    {
        $details = [
            'sku' => $this->price->sku,
            'priceData' => $this->price->getMagentoBasePrices(),
        ];

        if (is_a($exception, RequestException::class)) {
            $details['response'] = $exception->response->body();
        }

        Error::log()
            ->withGroup('Prices')
            ->withMessage('Failed to update prices in Magento for '.$this->price->sku)
            ->fromThrowable($exception)
            ->withDetails($details)
            ->withModel($this->price->getModel())
            ->save();

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
