<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Utility;

interface DeletesCurrentTierPrices
{
    public function delete(array $skus): void;
}
