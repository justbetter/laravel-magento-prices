<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;

class UpdatePriceJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $sku,
        protected ?string $type = null,
    ) {
        $this->queue = config('magento-prices.queue');
    }

    public function handle(ChecksMagentoExistence $checksMagentoExistence): void
    {
        $model = MagentoPrice::findBySku($this->sku);

        if ($model === null) {
            return;
        }

        if (! $checksMagentoExistence->exists($model->sku)) {
            $model->update([
                'update' => false,
                'sync' => false,
            ]);

            return;
        }

        $data = $model->getData();

        Bus::batch(array_filter([
            $this->handleBasePrices($data),
            $this->handleTierPrices($data),
            $this->handleSpecialPrices($data),
        ]))
            ->name('update-price-jobs')
            ->onQueue(config('magento-prices.queue'))
            ->then(function () use ($model): void {
                $model->update([
                    'fail_count' => 0,
                    'last_failed' => null,
                ]);
            })
            ->dispatch();

        $model->update([
            'update' => false,
        ]);

        UpdatedPriceEvent::dispatch($this->sku);
    }

    protected function handleBasePrices(PriceData $data): ?UpdateMagentoBasePricesJob
    {
        if ($this->shouldUpdateType('base') && $data->basePrices->isNotEmpty()) {
            return new UpdateMagentoBasePricesJob($data);
        }

        return null;
    }

    protected function handleTierPrices(PriceData $data): ?UpdateMagentoTierPricesJob
    {
        if ($this->shouldUpdateType('tier') && $data->tierPrices->isNotEmpty()) {
            $data->getModel()->update([
                'has_tier' => true,
            ]);

            return new UpdateMagentoTierPricesJob($data);
        }

        // Delete the tier price
        if ($data->getModel()->has_tier) {
            $data->getModel()->update([
                'has_tier' => false,
            ]);

            return new UpdateMagentoTierPricesJob($data);
        }

        return null;
    }

    protected function handleSpecialPrices(PriceData $data): ?UpdateMagentoSpecialPricesJob
    {
        if ($this->shouldUpdateType('special') && $data->specialPrices->isNotEmpty()) {
            $data->getModel()->update([
                'has_special' => true,
            ]);

            return new UpdateMagentoSpecialPricesJob($data);
        }

        // Delete the special price
        if ($data->getModel()->has_special) {
            $data->getModel()->update([
                'has_special' => false,
            ]);

            return new UpdateMagentoSpecialPricesJob($data);
        }

        return null;
    }

    protected function shouldUpdateType(string $type): bool
    {
        return blank($this->type) || $type == $this->type;
    }

    public function uniqueId(): string
    {
        return $this->sku;
    }

    public function tags(): array
    {
        return [
            $this->sku,
            'type:'.($this->type ?? 'all'),
        ];
    }
}
