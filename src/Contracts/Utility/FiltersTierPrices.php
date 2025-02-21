<?php

namespace JustBetter\MagentoPrices\Contracts\Utility;

interface FiltersTierPrices
{
    public function filter(string $sku, array $tierPrices): array;
}
