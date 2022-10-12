<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use JustBetter\MagentoPrices\Contracts\MonitorsWaitTimes;
use JustBetter\MagentoPrices\Jobs\MonitorWaitTimesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class MonitorWaitTimesJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(MonitorsWaitTimes::class, function (MockInterface $mock) {
            $mock->shouldReceive('monitor')->once();
        });

        MonitorWaitTimesJob::dispatchSync();
    }
}
