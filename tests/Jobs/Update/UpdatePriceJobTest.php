<?php

namespace JustBetter\MagentoPrices\Tests\Jobs\Update;

use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesPrice;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdatePriceJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(UpdatesPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        /** @var Price $price */
        $price = Price::query()->create(['sku' => '::sku::']);

        UpdatePriceJob::dispatch($price);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        /** @var Price $price */
        $price = Price::query()->create(['sku' => '::sku::']);

        $job = new UpdatePriceJob($price);

        $this->assertEquals($price->id, $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        /** @var Price $price */
        $price = Price::query()->create(['sku' => '::sku::']);

        $job = new UpdatePriceJob($price);

        $this->assertEquals(['::sku::'], $job->tags());
    }
}
