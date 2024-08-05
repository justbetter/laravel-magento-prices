<?php

namespace JustBetter\MagentoPrices\Contracts\Utility;

use JustBetter\MagentoPrices\Models\Price;

interface DeletesCurrentSpecialPrices
{
    public function delete(Price $price): void;
}
