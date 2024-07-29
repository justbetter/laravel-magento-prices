<?php

namespace JustBetter\MagentoPrices\Actions\Retrieval;

use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesPrice;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Jobs\Retrieval\SavePriceJob;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class RetrievePrice implements RetrievesPrice
{
    public function retrieve(string $sku, bool $forceUpdate): void
    {
        $repository = BaseRepository::resolve();

        $stockData = $repository->retrieve($sku);

        if ($stockData === null) {
            Price::query()
                ->where('sku', '=', $sku)
                ->update(['retrieve' => false]);

            return;
        }

        SavePriceJob::dispatch($stockData, $forceUpdate);
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesPrice::class, static::class);
    }
}
