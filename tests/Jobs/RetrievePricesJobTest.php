<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\RetrievePricesJob;
use JustBetter\MagentoPrices\Retriever\DummySkuRetriever;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class RetrievePricesJobTest extends TestCase
{
    public function test_it_dispatches_all_retrieve_job(): void
    {
        Bus::fake([RetrievePriceJob::class]);

        config()->set('magento-prices.retrievers.sku', DummySkuRetriever::class);

        $this->mock(DummySkuRetriever::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieveAll')
                ->andReturn(collect(['::sku_1::', '::sku_2::']));
        });

        RetrievePricesJob::dispatchSync();

        Bus::assertDispatchedTimes(RetrievePriceJob::class, 2);
    }

    public function test_it_dispatches_date_retrieve_job(): void
    {
        Bus::fake([RetrievePriceJob::class]);

        config()->set('magento-prices.retrievers.sku', DummySkuRetriever::class);

        $this->mock(DummySkuRetriever::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieveByDate')
                ->andReturn(collect());
        });

        RetrievePricesJob::dispatchSync(now());

        Bus::assertDispatchedTimes(RetrievePriceJob::class, 0);
    }

    public function test_queue_attributes_force_false(): void
    {
        $job = new RetrievePricesJob(null, false);

        $this->assertEquals(['force:false', 'all'], $job->tags());
    }

    public function test_queue_attributes_force_true(): void
    {
        $from = now();

        $job = new RetrievePricesJob($from, true);

        $this->assertEquals(['force:true', 'from:'.$from->toDateTimeString()], $job->tags());
    }
}
