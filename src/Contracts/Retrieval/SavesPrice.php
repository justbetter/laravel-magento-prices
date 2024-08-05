<?php

namespace JustBetter\MagentoPrices\Contracts\Retrieval;

use JustBetter\MagentoPrices\Data\PriceData;

interface SavesPrice
{
    public function save(PriceData $priceData, bool $forceUpdate): void;
}
