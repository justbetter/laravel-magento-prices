<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Jobs\Retrieval;

use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesAllPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrieveAllPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class RetrievePriceJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(RetrievesAllPrices::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retrieve')->once();
        });

        RetrieveAllPricesJob::dispatch();
    }
}
