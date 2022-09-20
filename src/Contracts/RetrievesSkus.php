<?php

namespace JustBetter\MagentoPrices\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Enumerable;

interface RetrievesSkus
{
    /** @return Enumerable<int, string> */
    public function retrieveAll(): Enumerable;

    /** @return Enumerable<int, string> */
    public function retrieveByDate(Carbon $from): Enumerable;
}
