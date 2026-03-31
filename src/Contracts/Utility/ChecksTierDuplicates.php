<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Utility;

use JustBetter\MagentoPrices\Models\Price;

interface ChecksTierDuplicates
{
    public function check(Price $model, array $tierPrices): void;
}
