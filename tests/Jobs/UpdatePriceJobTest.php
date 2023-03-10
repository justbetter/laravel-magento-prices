<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoBasePricesJob;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoSpecialPricesJob;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoTierPricesJob;
use JustBetter\MagentoPrices\Jobs\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use Mockery\MockInterface;

class UpdatePriceJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([
            UpdateMagentoBasePricesJob::class, UpdateMagentoTierPricesJob::class, UpdateMagentoSpecialPricesJob::class,
        ]);

        Event::fake();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')
                ->with('::sku::')
                ->andReturnTrue();
        });

        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'base_prices' => [['price' => 10, 'storeId' => 0]],
            'tier_prices' => [['price' => 10, 'storeId' => 0, 'customer_group' => 'ALL GROUPS', 'quantity' => 1]],
            'special_prices' => [['price' => 10, 'storeId' => 0, 'price_from' => null, 'price_to' => null]],
        ]);
    }

    public function test_it_checks_existence(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku_2::',
        ]);

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')
                ->with('::sku_2::')
                ->once()
                ->andReturnFalse();
        });

        UpdatePriceJob::dispatchSync('::sku_2::');

        $model = MagentoPrice::findBySku('::sku_2::');

        $this->assertFalse($model->update);
        $this->assertFalse($model->sync);
    }

    public function test_it_dispatches_update_jobs(): void
    {
        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertDispatched(UpdateMagentoBasePricesJob::class);
        Bus::assertDispatched(UpdateMagentoTierPricesJob::class);
        Bus::assertDispatched(UpdateMagentoSpecialPricesJob::class);

        $model = MagentoPrice::findBySku('::sku::');
        $this->assertFalse($model->update);
    }

    public function test_it_dispatches_updated_event(): void
    {
        UpdatePriceJob::dispatchSync('::sku::');

        Event::assertDispatched(UpdatedPriceEvent::class);
    }

    /** @dataProvider jobTypes */
    public function test_it_dispatches_update_jobs_by_type(string $type, string $dispatch, array $notDispatch): void
    {
        UpdatePriceJob::dispatchSync('::sku::', $type);

        Bus::assertDispatched($dispatch);

        foreach ($notDispatch as $jobClass) {
            Bus::assertNotDispatched($jobClass);
        }
    }

    public static function jobTypes(): array
    {
        return [
            [
                'type' => 'base',
                'dispatch' => UpdateMagentoBasePricesJob::class,
                'notDispatch' => [UpdateMagentoTierPricesJob::class, UpdateMagentoSpecialPricesJob::class],
            ],
            [
                'type' => 'tier',
                'dispatch' => UpdateMagentoTierPricesJob::class,
                'notDispatch' => [UpdateMagentoBasePricesJob::class, UpdateMagentoSpecialPricesJob::class],
            ],
            [
                'type' => 'special',
                'dispatch' => UpdateMagentoSpecialPricesJob::class,
                'notDispatch' => [UpdateMagentoBasePricesJob::class, UpdateMagentoTierPricesJob::class],
            ],
        ];
    }

    public function test_it_does_not_dispatch_empty_base(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['base_prices' => []]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertNotDispatched(UpdateMagentoBasePricesJob::class);
        Bus::assertDispatched(UpdateMagentoTierPricesJob::class);
        Bus::assertDispatched(UpdateMagentoSpecialPricesJob::class);
    }

    public function test_it_does_not_dispatch_empty_tier(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['tier_prices' => []]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertDispatched(UpdateMagentoBasePricesJob::class);
        Bus::assertNotDispatched(UpdateMagentoTierPricesJob::class);
        Bus::assertDispatched(UpdateMagentoSpecialPricesJob::class);
    }

    public function test_it_does_not_dispatch_empty_special(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['special_prices' => []]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertDispatched(UpdateMagentoBasePricesJob::class);
        Bus::assertDispatched(UpdateMagentoTierPricesJob::class);
        Bus::assertNotDispatched(UpdateMagentoSpecialPricesJob::class);
    }

    public function test_it_does_dispatch_empty_tier_to_remove_tier_prices(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['tier_prices' => [], 'has_tier' => true]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertDispatched(UpdateMagentoTierPricesJob::class);

        $model = MagentoPrice::findBySku('::sku::');
        $this->assertFalse($model->has_tier);
    }

    public function test_it_does_dispatch_empty_special_to_remove_special_prices(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['special_prices' => [], 'has_special' => true]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertDispatched(UpdateMagentoSpecialPricesJob::class);

        $model = MagentoPrice::findBySku('::sku::');
        $this->assertFalse($model->has_special);
    }

    public function test_queue_attributes(): void
    {
        $job = new UpdatePriceJob('::sku::');

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::', 'type:all'], $job->tags());

        $job = new UpdatePriceJob('::sku::', 'base');
        $this->assertEquals(['::sku::', 'type:base'], $job->tags());
    }

    public function test_it_does_nothing(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('exists');
        });

        UpdatePriceJob::dispatch('::non_existent::');
    }
}
