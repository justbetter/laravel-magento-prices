<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Update\Sync;

use JustBetter\MagentoPrices\Models\Price;

interface UpdatesPrice
{
    public function update(Price $price): void;
}
