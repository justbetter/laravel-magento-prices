<?php

namespace JustBetter\MagentoPrices\Actions\Retrieval;

use JustBetter\MagentoPrices\Contracts\Utility\ChecksTierDuplicates;
use JustBetter\MagentoPrices\Contracts\Retrieval\SavesPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Models\Price;

class SavePrice implements SavesPrice
{

    public function __construct(
        protected ChecksTierDuplicates $tierDuplicates
    )
    {
    }

    public function save(PriceData $priceData, bool $forceUpdate): void
    {

        /** @var Price $model */
        $model = Price::query()->firstOrNew([
            'sku' => $priceData['sku'],
        ]);

        $this->tierDuplicates->check($model, $priceData['tier_prices']);

        $model->base_prices = $priceData['base_prices'];
        $model->tier_prices = $priceData['tier_prices'];
        $model->special_prices = $priceData['special_prices'];

        $model->sync = true;
        $model->retrieve = false;
        $model->last_retrieved = now();

        $model->update = $forceUpdate || $model->checksum !== $priceData->checksum();
        $model->checksum = $priceData->checksum();

        $model->save();
    }

    public static function bind(): void
    {
        app()->singleton(SavesPrice::class, static::class);
    }
}
