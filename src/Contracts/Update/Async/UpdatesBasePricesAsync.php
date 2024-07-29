<?php

namespace JustBetter\MagentoPrices\Contracts\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Models\Price;

interface UpdatesBasePricesAsync
{
    /** @param Collection<int, Price> $prices */
    public function update(Collection $prices): void;
}
