<?php

namespace JustBetter\MagentoPrices\Contracts;

use JustBetter\MagentoPrices\Data\PriceData;

interface UpdatesMagentoTierPrice
{
    public function update(PriceData $priceData): void;
}
