<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Jobs;

use JustBetter\MagentoPrices\Contracts\ProcessesPrices;
use JustBetter\MagentoPrices\Jobs\ProcessPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class ProcessPricesJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(ProcessesPrices::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->once();
        });

        ProcessPricesJob::dispatch();
    }
}
