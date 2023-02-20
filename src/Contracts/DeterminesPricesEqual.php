<?php

namespace JustBetter\MagentoPrices\Contracts;

use JustBetter\MagentoPrices\Data\PriceData;

interface DeterminesPricesEqual
{
    public function equals(PriceData $a, PriceData $b): bool;
}
