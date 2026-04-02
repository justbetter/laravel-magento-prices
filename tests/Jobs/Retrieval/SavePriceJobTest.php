<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Jobs\Retrieval;

use JustBetter\MagentoPrices\Contracts\Retrieval\SavesPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Jobs\Retrieval\SavePriceJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class SavePriceJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(SavesPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('save')->once();
        });

        $priceData = PriceData::of(['sku' => '::sku::']);

        SavePriceJob::dispatch($priceData, false);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        $priceData = PriceData::of(['sku' => '::sku::']);

        $job = new SavePriceJob($priceData, false);

        $this->assertSame('::sku::', $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        $priceData = PriceData::of(['sku' => '::sku::']);

        $job = new SavePriceJob($priceData, false);

        $this->assertSame(['::sku::'], $job->tags());
    }
}
