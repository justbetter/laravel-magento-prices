<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\Utility\ChecksTierDuplicates;
use JustBetter\MagentoPrices\Exceptions\DuplicateTierPriceException;
use JustBetter\MagentoPrices\Models\Price;
use Spatie\Activitylog\ActivityLogger;

/**
 * Check if the tier prices have duplicates.
 * When this is the case, Magento will always reject the update.
 */
class CheckTierDuplicates implements ChecksTierDuplicates
{
    public function check(Price $model, array $tierPrices): void
    {
        $duplicates = collect($tierPrices)
            ->groupBy(['website_id', 'quantity', 'customer_group'])
            ->flatten(2)
            ->filter(fn (Collection $matches): bool => $matches->count() > 1);

        if ($duplicates->isEmpty()) {
            return;
        }

        activity()
            ->when($model->exists, fn (ActivityLogger $logger): ActivityLogger => $logger->on($model))
            ->useLog('error')
            ->withProperties([
                'duplicate' => $duplicates->toArray(),
            ])
            ->log("Duplicate tier prices found for $model->sku");

        throw new DuplicateTierPriceException("Duplicate tier prices found for $model->sku. Duplicates: ".json_encode($duplicates->toArray()));
    }

    public static function bind(): void
    {
        app()->singleton(ChecksTierDuplicates::class, static::class);
    }
}
