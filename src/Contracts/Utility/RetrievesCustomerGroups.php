<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Contracts\Utility;

interface RetrievesCustomerGroups
{
    public function retrieve(): array;
}
