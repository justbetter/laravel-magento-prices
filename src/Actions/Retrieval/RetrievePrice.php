<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Actions\Retrieval;

use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Jobs\Retrieval\SavePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class RetrievePrice implements RetrievesPrice
{
    public function retrieve(string $sku, bool $forceUpdate): void
    {
        $repository = BaseRepository::resolve();

        $priceData = $repository->retrieve($sku);

        if (! $priceData instanceof PriceData) {
            Price::query()
                ->where('sku', '=', $sku)
                ->update(['retrieve' => false]);

            return;
        }

        SavePriceJob::dispatch($priceData, $forceUpdate);
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesPrice::class, static::class);
    }
}
