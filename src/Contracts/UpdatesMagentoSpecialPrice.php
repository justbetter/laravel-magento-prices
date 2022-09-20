<?php

namespace JustBetter\MagentoPrices\Contracts;

use JustBetter\MagentoPrices\Data\PriceData;

interface UpdatesMagentoSpecialPrice
{
    public function update(PriceData $priceData): void;
}
