<?php

namespace JustBetter\MagentoPrices\Actions\Retrieval;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesAllPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class RetrieveAllPrices implements RetrievesAllPrices
{
    public function retrieve(?Carbon $from): void
    {
        $repository = BaseRepository::resolve();

        $repository->skus($from)->each(fn (string $sku): PendingDispatch => RetrievePriceJob::dispatch($sku));
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesAllPrices::class, static::class);
    }
}
