<?php

namespace JustBetter\MagentoPrices\Contracts\Update\Sync;

use JustBetter\MagentoPrices\Models\Price;

interface UpdatesPrice
{
    public function update(Price $price): void;
}
