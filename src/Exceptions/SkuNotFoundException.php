<?php

namespace JustBetter\MagentoPrices\Exceptions;

use Exception;

class SkuNotFoundException extends Exception
{
    public function __construct(string $sku)
    {
        parent::__construct("SKU $sku does not exist");
    }
}
