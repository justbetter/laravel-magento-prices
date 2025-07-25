<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Actions\Retrieval\RetrieveAllPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\Fakes\FakeRepository;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use PHPUnit\Framework\Attributes\Test;

class RetrieveAllPricesTest extends TestCase
{
    #[Test]
    public function it_dispatches_jobs(): void
    {
        config()->set('magento-prices.repository', FakeRepository::class);

        Bus::fake();

        MagentoProduct::query()->create(['sku' => '::sku::', 'exists_in_magento' => true]);

        /** @var RetrieveAllPrices $action */
        $action = app(RetrieveAllPrices::class);
        $action->retrieve(null, false);

        Bus::assertDispatched(RetrievePriceJob::class);
    }

    #[Test]
    public function it_defers_retrievals(): void
    {
        config()->set('magento-prices.repository', FakeRepository::class);

        Bus::fake();

        MagentoProduct::query()->create(['sku' => '::sku-1::', 'exists_in_magento' => true]);
        MagentoProduct::query()->create(['sku' => '::sku-2::', 'exists_in_magento' => true]);
        MagentoProduct::query()->create(['sku' => '::sku-3::', 'exists_in_magento' => true]);

        Price::query()->create(['sku' => '::sku-1::', 'retrieve' => false]);

        /** @var RetrieveAllPrices $action */
        $action = app(RetrieveAllPrices::class);
        $action->retrieve(null, true);

        Bus::assertNotDispatched(RetrievePriceJob::class);

        $prices = Price::query()
            ->where('retrieve', '=', true)
            ->pluck('sku');

        $this->assertEquals([
            '::sku-1::',
            '::sku-2::',
            '::sku-3::',
        ], $prices->toArray());
    }
}
