<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Models\BulkRequest;
use JustBetter\MagentoClient\Contracts\ChecksMagento;
use JustBetter\MagentoPrices\Actions\ProcessPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Models\MagentoProduct;
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

        MagentoProduct::query()->create([
            'sku' => '::sku::',
            'exists_in_magento' => true,
        ]);

        Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessPrices $action */
        $action = app(ProcessPrices::class);
        $action->process();

        Bus::assertDispatched(UpdatePricesAsyncJob::class, function (UpdatePricesAsyncJob $job): bool {
            return $job->prices->count() === 1;
        });
    }

    #[Test]
    public function it_does_not_dispatch_prices_with_open_async_operations(): void
    {
        Bus::fake();
        config()->set('magento-prices.async', true);

        MagentoProduct::query()->create([
            'sku' => '::sku_1::',
            'exists_in_magento' => true,
        ]);

        MagentoProduct::query()->create([
            'sku' => '::sku_2::',
            'exists_in_magento' => true,
        ]);

        /** @var Price $price */
        $price = Price::query()->create([
            'sku' => '::sku_1::',
            'update' => true,
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'magento_connection' => '::magento-connection::',
            'store_code' => '::store-code::',
            'method' => 'POST',
            'path' => '::path::',
            'bulk_uuid' => '::bulk-uuid-1::',
            'request' => [
                [
                    'call-1',
                ],
            ],
            'response' => [],
            'created_at' => now(),
        ]);

        $request->operations()->create([
            'operation_id' => 0,
            'subject_type' => $price->getMorphClass(),
            'subject_id' => $price->getKey(),
            'status' => OperationStatus::Open,
        ]);

        Price::query()->create([
            'sku' => '::sku_2::',
            'update' => true,
        ]);

        /** @var ProcessPrices $action */
        $action = app(ProcessPrices::class);
        $action->process();

        Bus::assertDispatched(UpdatePricesAsyncJob::class, function (UpdatePricesAsyncJob $job): bool {
            return $job->prices->count() === 1 && $job->prices->first()?->sku === '::sku_2::';
        });
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
