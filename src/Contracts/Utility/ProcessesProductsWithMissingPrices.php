<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Utility;

interface ProcessesProductsWithMissingPrices
{
    public function process(): void;
}
