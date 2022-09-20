<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\ChecksTierDuplicates;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Exceptions\DuplicateTierPriceException;

/**
 * Check if the tier prices have duplicates.
 * When this is the case, Magento will always reject the update.
 */
class CheckTierDuplicates implements ChecksTierDuplicates
{
    public function check(string $sku, Collection $tierPrices): void
    {
        /** @var TierPriceData $tierPrice */
        foreach ($tierPrices as $tierPrice) {
            $matching = $tierPrices
                ->where('storeId', $tierPrice->storeId)
                ->where('quantity', $tierPrice->quantity)
                ->where('groupId', $tierPrice->groupId);

            if ($matching->count() == 1) {
                continue;
            }

            throw new DuplicateTierPriceException($sku, $matching);
        }
    }

    public static function bind(): void
    {
        app()->singleton(ChecksTierDuplicates::class, static::class);
    }
}
