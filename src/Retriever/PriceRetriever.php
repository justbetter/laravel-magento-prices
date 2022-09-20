<?php

namespace JustBetter\MagentoPrices\Retriever;

use JustBetter\MagentoPrices\Contracts\RetrievesPrice;
use JustBetter\MagentoPrices\Data\PriceData;

abstract class PriceRetriever implements RetrievesPrice
{
    abstract public function retrieve(string $sku): ?PriceData;
}
