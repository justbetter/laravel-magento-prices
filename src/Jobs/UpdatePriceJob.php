<?php

namespace JustBetter\MagentoPrices\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;

class UpdatePriceJob implements ShouldQueue, ShouldBeUnique
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

        $this->handleBasePrices($data);
        $this->handleTierPrices($data);
        $this->handleSpecialPrices($data);

        $model->update([
            'update' => false,
        ]);
    }

    protected function handleBasePrices(PriceData $data): void
    {
        if ($this->shouldUpdateType('base') && $data->basePrices->isNotEmpty()) {
            UpdateMagentoBasePricesJob::dispatch($data);
        }
    }

    protected function handleTierPrices(PriceData $data): void
    {
        if ($this->shouldUpdateType('tier') && $data->tierPrices->isNotEmpty()) {
            UpdateMagentoTierPricesJob::dispatch($data);

            $data->getModel()->update([
                'has_tier' => true,
            ]);

            return;
        }

        // Delete the tier price
        if ($data->getModel()->has_tier) {
            UpdateMagentoTierPricesJob::dispatch($data);

            $data->getModel()->update([
                'has_tier' => false,
            ]);
        }
    }

    protected function handleSpecialPrices(PriceData $data): void
    {
        if ($this->shouldUpdateType('special') && $data->specialPrices->isNotEmpty()) {
            UpdateMagentoSpecialPricesJob::dispatch($data);

            $data->getModel()->update([
                'has_special' => true,
            ]);

            return;
        }

        // Delete the special price
        if ($data->getModel()->has_special) {
            UpdateMagentoSpecialPricesJob::dispatch($data);

            $data->getModel()->update([
                'has_special' => false,
            ]);
        }
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
