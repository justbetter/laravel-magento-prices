<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Jobs\ProcessPricesJob;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Retriever\DummyPriceRetriever;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class RetrievePriceJobTest extends TestCase
{
    public function test_it_dispatches_process_job(): void
    {
        Bus::fake([ProcessPricesJob::class]);

        config()->set('magento-prices.retrievers.price', DummyPriceRetriever::class);

        $this->mock(DummyPriceRetriever::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')
                ->andReturn(new PriceData('::sku::', collect()));
        });

        RetrievePriceJob::dispatchSync('::sku::');

        Bus::assertDispatched(ProcessPricesJob::class);
    }

    public function test_it_does_not_dispatch_process_job(): void
    {
        Bus::fake([ProcessPricesJob::class]);

        config()->set('magento-prices.retrievers.price', DummyPriceRetriever::class);

        $this->mock(DummyPriceRetriever::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')
                ->andReturnNull();
        });

        RetrievePriceJob::dispatchSync('::sku::');

        Bus::assertNotDispatched(ProcessPricesJob::class);
    }

    public function test_queue_attributes_force_false(): void
    {
        $job = new RetrievePriceJob('::sku::', false);

        $this->assertEquals(['::sku::', 'force:false'], $job->tags());
    }

    public function test_queue_attributes_force_true(): void
    {
        $job = new RetrievePriceJob('::sku::', true);

        $this->assertEquals(['::sku::', 'force:true'], $job->tags());
    }
}
