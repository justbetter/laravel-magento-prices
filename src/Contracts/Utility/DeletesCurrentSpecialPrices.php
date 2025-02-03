<?php

namespace JustBetter\MagentoPrices\Contracts\Utility;

interface DeletesCurrentSpecialPrices
{
    public function delete(array $skus): void;
}
