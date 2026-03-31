<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Utility;

interface DeletesCurrentSpecialPrices
{
    public function delete(array $skus): void;
}
