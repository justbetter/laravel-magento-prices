<?php

namespace JustBetter\MagentoPrices\Contracts\Utility;

use Illuminate\Support\Enumerable;

interface FindsProductsWithMissingPrices
{
    /** @return Enumerable<int, string> list of skus */
    public function retrieve(): Enumerable;
}
