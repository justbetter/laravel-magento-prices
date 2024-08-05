<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Utility\ProcessProductsWithMissingPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProcessProductsWithMissingPricesTest extends TestCase
{
    #[Test]
    public function it_dispatches_update_jobs_for_missing_prices(): void
    {
        Magento::fake();
        Bus::fake();

        Http::fake([
            'magento/rest/all/V1/products?fields=sku%2Cprice%2Ctype_id&searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => Http::response([
                'items' => [
                    [
                        'sku' => '::sku_1::',
                        'price' => 10,
                        'type_id' => 'simple',
                    ],
                    [
                        'sku' => '::sku_2::',
                        'price' => 0,
                        'type_id' => 'simple',
                    ],
                    [
                        'sku' => '::sku_3::',
                        'type_id' => 'simple',
                    ],
                ],
            ]),
        ])->preventStrayRequests();

        Price::query()->create(['sku' => '::sku_3::']);

        /** @var ProcessProductsWithMissingPrices $action */
        $action = app(ProcessProductsWithMissingPrices::class);
        $action->process();

        Bus::assertDispatched(UpdatePriceJob::class, function (UpdatePriceJob $job): bool {
            return $job->price->sku === '::sku_3::';
        });

        Bus::assertDispatched(RetrievePriceJob::class, function (RetrievePriceJob $job): bool {
            return $job->sku === '::sku_2::';
        });
    }

    #[Test]
    public function it_dispatches_update_jobs_async(): void
    {
        config()->set('magento-prices.async', true);

        Magento::fake();
        Bus::fake();

        Http::fake([
            'magento/rest/all/V1/products?fields=sku%2Cprice%2Ctype_id&searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => Http::response([
                'items' => [
                    [
                        'sku' => '::sku_1::',
                        'price' => 10,
                        'type_id' => 'simple',
                    ],
                    [
                        'sku' => '::sku_2::',
                        'price' => 0,
                        'type_id' => 'simple',
                    ],
                ],
            ]),
        ])->preventStrayRequests();

        Price::query()->create(['sku' => '::sku_2::']);

        /** @var ProcessProductsWithMissingPrices $action */
        $action = app(ProcessProductsWithMissingPrices::class);
        $action->process();

        Bus::assertDispatched(UpdatePricesAsyncJob::class, function (UpdatePricesAsyncJob $job): bool {
            return $job->prices->pluck('sku')->toArray() === ['::sku_2::'];
        });
    }
}
