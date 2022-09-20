<?php

namespace JustBetter\MagentoPrices\Contracts;

use JustBetter\MagentoPrices\Data\PriceData;

interface RetrievesPrice
{
    public function retrieve(string $sku): ?PriceData;
}
