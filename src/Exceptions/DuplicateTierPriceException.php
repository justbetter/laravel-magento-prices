<?php

namespace JustBetter\MagentoPrices\Exceptions;

use Exception;
use Illuminate\Support\Collection;

class DuplicateTierPriceException extends Exception
{
    public function __construct(string $sku, Collection $tierPrices)
    {
        $message = "Found duplicate tier prices for $sku:".PHP_EOL;

        foreach ($tierPrices as $tier) {
            $message .= "Store: $tier->storeId, Group: $tier->groupId, Qty: $tier->quantity.".PHP_EOL;
        }

        parent::__construct($message);
    }
}
