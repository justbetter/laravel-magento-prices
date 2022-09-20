<?php

namespace JustBetter\MagentoPrices\Retriever;

use Carbon\Carbon;
use Illuminate\Support\Enumerable;

class DummySkuRetriever extends SkuRetriever
{
    public function retrieveAll(): Enumerable
    {
        return collect([
            '123',
            '456',
        ]);
    }

    public function retrieveByDate(Carbon $from): Enumerable
    {
        return collect([
            '789',
        ]);
    }
}
