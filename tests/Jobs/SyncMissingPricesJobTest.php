<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Contracts\Utility\FindsProductsWithMissingPrices;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Utility\SyncMissingPricesJob;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class SyncMissingPricesJobTest extends TestCase
{
    public function test_it_dispatches_jobs(): void
    {
        Bus::fake([UpdatePriceJob::class, RetrievePriceJob::class]);

        MagentoPrice::query()
            ->create(['sku' => '::sku_1::']);

        $this->mock(FindsProductsWithMissingPrices::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')
                ->andReturn(collect(['::sku_1::', '::sku_2::']));
        });

        SyncMissingPricesJob::dispatchSync();

        Bus::assertDispatched(UpdatePriceJob::class, function (UpdatePriceJob $job) {
            return $job->sku === '::sku_1::';
        });

        Bus::assertDispatched(RetrievePriceJob::class, function (RetrievePriceJob $job) {
            return $job->sku === '::sku_2::';
        });
    }
}
