<?php

namespace JustBetter\MagentoPrices\Contracts\Update\Sync;

use JustBetter\MagentoPrices\Models\Price;

interface UpdatesTierPrice
{
    public function update(Price $price): bool;
}
