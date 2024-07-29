<?php

namespace JustBetter\MagentoPrices\Contracts\Retrieval;

use Illuminate\Support\Carbon;

interface RetrievesAllPrices
{
    public function retrieve(?Carbon $from): void;
}
