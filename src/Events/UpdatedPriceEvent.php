<?php

namespace JustBetter\MagentoPrices\Events;

use Illuminate\Foundation\Events\Dispatchable;
use JustBetter\MagentoPrices\Models\Price;

class UpdatedPriceEvent
{
    use Dispatchable;

    public function __construct(public Price $price)
    {
    }
}
