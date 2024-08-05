<?php

namespace JustBetter\MagentoPrices\Contracts\Retrieval;

interface RetrievesPrice
{
    public function retrieve(string $sku, bool $forceUpdate): void;
}
