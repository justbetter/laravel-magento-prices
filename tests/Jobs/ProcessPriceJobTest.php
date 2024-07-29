<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use JustBetter\MagentoPrices\Contracts\ProcessesPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Jobs\ProcessPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class ProcessPriceJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(ProcessesPrice::class, function (MockInterface $mock) {
            $mock->shouldReceive('process')->once();
        });

        ProcessPricesJob::dispatchSync(new PriceData('::sku::', collect()));
    }

    public function test_queue_attributes(): void
    {
        $job = new ProcessPricesJob(new PriceData('::sku::', collect()));

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::'], $job->tags());
    }
}
