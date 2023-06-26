<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoPrices\Contracts\UpdatesMagentoTierPrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoTierPricesJob;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class UpdateMagentoTierPricesJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(UpdatesMagentoTierPrice::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->once();
        });

        UpdateMagentoTierPricesJob::dispatchSync(new PriceData('::sku::', collect()));
    }

    public function test_queue_attributes(): void
    {
        $job = new UpdateMagentoTierPricesJob(new PriceData('::sku::', collect()));

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::'], $job->tags());
    }

    public function test_it_handles_failure(): void
    {
        $job = new UpdateMagentoTierPricesJob(new PriceData('::sku::', collect()));

        $exception = (new Response(new \GuzzleHttp\Psr7\Response(500)))->toException();

        $job->failed($exception);

        $model = MagentoPrice::findBySku('::sku::');

        $this->assertEquals(1, $model->fail_count);
        $this->assertNotNull($model->last_failed);
    }
}
