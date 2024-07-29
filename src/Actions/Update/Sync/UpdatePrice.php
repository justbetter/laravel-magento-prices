<?php

namespace JustBetter\MagentoPrices\Actions\Update\Sync;

use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesBasePrice;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesPrice;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesSpecialPrice;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesTierPrice;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;

class UpdatePrice implements UpdatesPrice
{
    public function __construct(
        protected ChecksMagentoExistence $magentoExistence,
        protected UpdatesBasePrice $basePrice,
        protected UpdatesTierPrice $tierPrice,
        protected UpdatesSpecialPrice $specialPrice,
    ) {
    }

    public function update(Price $price): void
    {
        if (! $this->magentoExistence->exists($price->sku)) {
            $price->update([
                'update' => false,
            ]);

            return;
        }

        $results = [];

        $results[] = $this->basePrice->update($price);
        $results[] = $this->tierPrice->update($price);
        $results[] = $this->specialPrice->update($price);

        $hasFailure = in_array(false, $results);

        if ($hasFailure) {
            $price->registerFailure();
            return;
        }

        $price->update([
            'last_updated' => now(),
            'update' => false,
        ]);

        event(new UpdatedPriceEvent($price));

    }

    public static function bind(): void
    {
        app()->singleton(UpdatesPrice::class, static::class);
    }
}
