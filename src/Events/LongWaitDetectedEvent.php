<?php

namespace JustBetter\MagentoPrices\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LongWaitDetectedEvent
{
    use Dispatchable;

    public function __construct(public string $type, public int $wait)
    {
    }
}
