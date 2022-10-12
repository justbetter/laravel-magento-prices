<?php

namespace JustBetter\MagentoPrices\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LongWaitDetected
{
    use Dispatchable;

    public function __construct(public string $type, public int $wait)
    {
    }
}
