<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Jobs\Retrieval;

use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesPrice;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class RetrieveAllPricesJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(RetrievesPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retrieve')->once();
        });

        RetrievePriceJob::dispatch('::sku::', false);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        $job = new RetrievePriceJob('::sku::', false);

        $this->assertSame('::sku::', $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        $job = new RetrievePriceJob('::sku::', false);

        $this->assertSame(['::sku::'], $job->tags());
    }
}
