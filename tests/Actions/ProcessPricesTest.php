<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoClient\Contracts\ChecksMagento;
use JustBetter\MagentoPrices\Actions\ProcessPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ProcessPricesTest extends TestCase
{
    #[Test]
    public function it_dispatches_retrieval_jobs(): void
    {
        Bus::fake();

        Price::query()->create([
            'sku' => '::sku::',
            'retrieve' => true,
        ]);

        /** @var ProcessPrices $action */
        $action = app(ProcessPrices::class);
        $action->process();

        Bus::assertDispatched(RetrievePriceJob::class);
        Bus::assertNotDispatched(UpdatePriceJob::class);
    }

    #[Test]
    public function it_dispatches_update_jobs(): void
    {
        Bus::fake();

        Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessPrices $action */
        $action = app(ProcessPrices::class);
        $action->process();

        Bus::assertNotDispatched(RetrievePriceJob::class);
        Bus::assertDispatched(UpdatePriceJob::class);
    }

    #[Test]
    public function it_dispatches_async_update_job(): void
    {
        Bus::fake();
        config()->set('magento-prices.async', true);

        Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessPrices $action */
        $action = app(ProcessPrices::class);
        $action->process();

        Bus::assertDispatched(UpdatePricesAsyncJob::class);
    }

    #[Test]
    public function it_does_not_dispatch_update_jobs_if_magento_is_unavailable(): void
    {
        Bus::fake();

        $this->mock(ChecksMagento::class, function (MockInterface $mock): void {
            $mock->shouldReceive('available')->andReturnFalse();
        });

        Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessPrices $action */
        $action = app(ProcessPrices::class);
        $action->process();

        Bus::assertNotDispatched(UpdatePriceJob::class);
    }
}
