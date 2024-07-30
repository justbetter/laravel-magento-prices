<?php

namespace JustBetter\MagentoPrices\Tests\Jobs\Update;

use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesPricesAsync;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdatePricesAsyncJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(UpdatesPricesAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        UpdatePricesAsyncJob::dispatch(collect());
    }

    #[Test]
    public function it_has_tags(): void
    {
        $job = new UpdatePricesAsyncJob(collect([
            ['sku' => '::sku_1::'],
            ['sku' => '::sku_2::'],
        ]));

        $this->assertEquals(['::sku_1::', '::sku_2::'], $job->tags());
    }
}
