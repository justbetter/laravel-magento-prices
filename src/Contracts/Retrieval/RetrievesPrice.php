<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Retrieval;

interface RetrievesPrice
{
    public function retrieve(string $sku, bool $forceUpdate): void;
}
