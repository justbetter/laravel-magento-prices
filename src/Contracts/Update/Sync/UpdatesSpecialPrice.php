<?php

namespace JustBetter\MagentoPrices\Contracts\Update\Sync;

use JustBetter\MagentoPrices\Models\Price;

interface UpdatesSpecialPrice
{
    public function update(Price $price): bool;
}
