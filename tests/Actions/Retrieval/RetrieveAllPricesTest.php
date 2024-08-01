<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Actions\Retrieval\RetrieveAllPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
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
        $action->retrieve(null);

        Bus::assertDispatched(RetrievePriceJob::class);
    }
}
