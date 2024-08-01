<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Actions\Retrieval\RetrievePrice;
use JustBetter\MagentoPrices\Jobs\Retrieval\SavePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\Fakes\FakeNullRepository;
use JustBetter\MagentoPrices\Tests\Fakes\FakeRepository;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrievePriceTest extends TestCase
{
    #[Test]
    public function it_sets_retrieve_when_no_pricedata(): void
    {
        config()->set('magento-prices.repository', FakeNullRepository::class);

        /** @var Price $model */
        $model = Price::query()
            ->create([
                'sku' => '::sku::',
                'retrieve' => true,
            ]);

        /** @var RetrievePrice $action */
        $action = app(RetrievePrice::class);
        $action->retrieve('::sku::', false);

        $this->assertFalse($model->refresh()->retrieve);
    }

    #[Test]
    public function it_dispatches_save_job(): void
    {
        config()->set('magento-prices.repository', FakeRepository::class);
        Bus::fake();

        Price::query()
            ->create([
                'sku' => '::sku::',
            ]);

        /** @var RetrievePrice $action */
        $action = app(RetrievePrice::class);
        $action->retrieve('::sku::', true);

        Bus::assertDispatched(SavePriceJob::class, function (SavePriceJob $job): bool {
            return $job->data['sku'] === '::sku::' && $job->forceUpdate;
        });
    }
}
