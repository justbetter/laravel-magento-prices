<?php

namespace JustBetter\MagentoPrices\Contracts\Update\Sync;

use JustBetter\MagentoPrices\Models\Price;

interface UpdatesBasePrice
{
    public function update(Price $price): bool;
}
