<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Jobs\Update;

use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesPricesAsync;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class UpdatePricesAsyncJobTest extends TestCase
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
        $prices = collect([
            Price::query()->create(['sku' => '::sku_1::']),
            Price::query()->create(['sku' => '::sku_2::']),
        ]);
        $job = new UpdatePricesAsyncJob($prices);

        $this->assertSame(['::sku_1::', '::sku_2::'], $job->tags());
    }
}
