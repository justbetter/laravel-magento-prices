<?php

namespace JustBetter\MagentoPrices\Contracts;

use JustBetter\MagentoPrices\Data\PriceData;

interface UpdatesMagentoBasePrice
{
    public function update(PriceData $priceData): void;
}
