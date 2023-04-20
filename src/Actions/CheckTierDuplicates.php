<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Support\Collection;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoPrices\Contracts\ChecksTierDuplicates;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Exceptions\DuplicateTierPriceException;
use JustBetter\MagentoPrices\Models\MagentoPrice;

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

            /** @var ?MagentoPrice $model */
            $model = MagentoPrice::query()->firstWhere('sku', '=', $sku);

            $error = Error::log()
                ->withGroup('Prices')
                ->withMessage("Duplicate tier prices found for $sku!")
                ->withDetails([
                    'sku' => $sku,
                    'duplicate' => $matching->toArray()
                ]);

            if ($model !== null) {
                $error->withModel($model);
            }

            $error->save();

            throw new DuplicateTierPriceException($sku, $matching);
        }
    }

    public static function bind(): void
    {
        app()->singleton(ChecksTierDuplicates::class, static::class);
    }
}
