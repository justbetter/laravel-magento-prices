<?php

namespace JustBetter\MagentoPrices\Contracts;

interface SyncsPrices
{
    public function sync(int $retrieveLimit = null, int $updateLimit = null): void;
}
