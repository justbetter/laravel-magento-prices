<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoPrices\Contracts\ProcessesPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;

class ProcessPrice implements ProcessesPrice
{
    public function __construct(
        protected ChecksMagentoExistence $checksMagentoExistence
    ) {
    }

    public function process(PriceData $price, bool $forceUpdate = false): void
    {
        $price->validate();

        $priceModel = $price->getModel();
        $prices = $price;

        $currentPrices = $priceModel->getData();

        $priceModel->base_prices = $prices->basePrices;
        $priceModel->tier_prices = $prices->tierPrices;
        $priceModel->special_prices = $prices->specialPrices;

        $priceModel->last_retrieved = now();
        $priceModel->retrieve = false;

        $priceModel->update = $forceUpdate || ! $currentPrices->equals($price);

        if (! $priceModel->sync && $priceModel->update && $this->checksMagentoExistence->exists($priceModel->sku)) {
            $priceModel->sync = true;
        }

        $priceModel->save();
    }

    public static function bind(): void
    {
        app()->singleton(ProcessesPrice::class, static::class);
    }
}
