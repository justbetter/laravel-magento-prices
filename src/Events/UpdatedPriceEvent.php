<?php

namespace JustBetter\MagentoPrices\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UpdatedPriceEvent
{
    use Dispatchable;

    public function __construct(public string $sku)
    {
    }
}
