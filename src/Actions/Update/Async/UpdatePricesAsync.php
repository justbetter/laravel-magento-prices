<?php

namespace JustBetter\MagentoPrices\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesBasePricesAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesPricesAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesSpecialPricesAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesTierPricesAsync;
use JustBetter\MagentoPrices\Models\Price;

class UpdatePricesAsync implements UpdatesPricesAsync
{
    public function __construct(
        protected UpdatesBasePricesAsync $basePrice,
        protected UpdatesTierPricesAsync $tierPrice,
        protected UpdatesSpecialPricesAsync $specialPrice,
    ) {}

    public function update(Collection $prices): void
    {
        $this->basePrice->update($prices);
        $this->tierPrice->update($prices);
        $this->specialPrice->update($prices);

        $prices->each(fn (Price $price): bool => $price->update(['update' => false]));
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesPricesAsync::class, static::class);
    }
}
