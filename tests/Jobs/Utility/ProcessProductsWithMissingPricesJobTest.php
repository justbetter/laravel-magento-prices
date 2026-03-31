<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Jobs\Utility;

use JustBetter\MagentoPrices\Contracts\Utility\ProcessesProductsWithMissingPrices;
use JustBetter\MagentoPrices\Jobs\Utility\ProcessProductsWithMissingPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class ProcessProductsWithMissingPricesJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(ProcessesProductsWithMissingPrices::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->once();
        });

        ProcessProductsWithMissingPricesJob::dispatch();
    }
}
