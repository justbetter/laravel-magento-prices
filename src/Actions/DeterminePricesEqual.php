<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\DeterminesPricesEqual;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;

class DeterminePricesEqual implements DeterminesPricesEqual
{
    public function equals(PriceData $a, PriceData $b): bool
    {
        if (! $this->basePricesEqual($a->basePrices, $b->basePrices)) {
            return false;
        }

        if (! $this->tierPricesEqual($a->tierPrices, $b->tierPrices)) {
            return false;
        }

        if (! $this->specialPriceEqual($a->specialPrices, $b->specialPrices)) {
            return false;
        }

        return true;
    }

    protected function basePricesEqual(Collection $a, Collection $b): bool
    {
        if ($a->count() !== $b->count()) {
            return false;
        }

        /** @var BasePriceData $basePrice */
        foreach ($a as $basePrice) {
            /** @var ?BasePriceData $matchingPrice */
            $matchingPrice = $b->where('storeId', '=', $basePrice->storeId)->first();

            if ($matchingPrice === null) {
                return false;
            }

            if (! $basePrice->equals($matchingPrice)) {
                return false;
            }
        }

        return true;
    }

    protected function tierPricesEqual(Collection $a, Collection $b): bool
    {
        if ($a->count() !== $b->count()) {
            return false;
        }

        /** @var TierPriceData $tierPrice */
        foreach ($a as $tierPrice) {
            /** @var ?TierPriceData $matchingPrice */
            $matchingPrice = $b
                ->where('groupId', '=', $tierPrice->groupId)
                ->where('storeId', '=', $tierPrice->storeId)
                ->where('quantity', '=', $tierPrice->quantity)
                ->where('priceType', '=', $tierPrice->priceType)
                ->first();

            if ($matchingPrice === null) {
                return false;
            }

            if (! $tierPrice->equals($matchingPrice)) {
                return false;
            }
        }

        return true;
    }

    protected function specialPriceEqual(Collection $a, Collection $b): bool
    {
        if ($a->count() !== $b->count()) {
            return false;
        }

        /** @var SpecialPriceData $specialPrice */
        foreach ($a as $specialPrice) {
            /** @var ?SpecialPriceData $matchingPrice */
            $matchingPrice = $b
                ->where('storeId', '=', $specialPrice->storeId)
                ->first();

            if ($matchingPrice === null) {
                return false;
            }

            if (! $specialPrice->equals($matchingPrice)) {
                return false;
            }
        }

        return true;
    }

    public static function bind(): void
    {
        app()->singleton(DeterminesPricesEqual::class, static::class);
    }
}
