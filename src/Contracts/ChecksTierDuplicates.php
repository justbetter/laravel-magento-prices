<?php

namespace JustBetter\MagentoPrices\Contracts;

use Illuminate\Support\Collection;

interface ChecksTierDuplicates
{
    public function check(string $sku, Collection $tierPrices): void;
}
