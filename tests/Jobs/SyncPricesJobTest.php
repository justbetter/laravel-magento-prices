<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use JustBetter\MagentoPrices\Contracts\SyncsPrices;
use JustBetter\MagentoPrices\Jobs\SyncPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class SyncPricesJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(SyncsPrices::class, function (MockInterface $mock) {
            $mock->shouldReceive('sync')->once();
        });

        SyncPricesJob::dispatchSync();
    }
}
