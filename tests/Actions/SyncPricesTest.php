<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Actions\SyncPrices;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;

class SyncPricesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([RetrievePriceJob::class, UpdatePriceJob::class]);
    }

    public function test_it_resets_double_state(): void
    {
        MagentoPrice::create([
            'sku' => '::sku_1::',
            'retrieve' => true,
            'update' => true,
        ]);

        MagentoPrice::create([
            'sku' => '::sku_2::',
            'retrieve' => true,
            'update' => true,
        ]);

        $action = new SyncPrices();
        $action->sync();

        $this->assertEquals(2, MagentoPrice::shouldRetrieve()->count());
        $this->assertEquals(0, MagentoPrice::shouldUpdate()->count());
    }

    public function test_it_dispatches_single_retrieve_job(): void
    {
        MagentoPrice::create([
            'sku' => '::sku_1::',
            'retrieve' => true,
            'update' => false,
        ]);

        $action = new SyncPrices();
        $action->sync();

        Bus::assertDispatched(RetrievePriceJob::class);
    }

    public function test_it_dispatches_single_update_job(): void
    {
        MagentoPrice::create([
            'sku' => '::sku_1::',
            'retrieve' => false,
            'update' => true,
        ]);

        $action = new SyncPrices();
        $action->sync();

        Bus::assertDispatched(UpdatePriceJob::class);
    }
}
