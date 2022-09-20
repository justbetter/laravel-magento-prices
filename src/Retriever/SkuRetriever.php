<?php

namespace JustBetter\MagentoPrices\Retriever;

use Carbon\Carbon;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoPrices\Contracts\RetrievesSkus;

abstract class SkuRetriever implements RetrievesSkus
{
    abstract public function retrieveAll(): Enumerable;

    public function retrieveByDate(Carbon $from): Enumerable
    {
        return collect();
    }
}
