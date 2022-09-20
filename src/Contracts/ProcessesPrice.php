<?php

namespace JustBetter\MagentoPrices\Contracts;

use JustBetter\MagentoPrices\Data\PriceData;

interface ProcessesPrice
{
    public function process(PriceData $priceData, bool $forceUpdate = false): void;
}
