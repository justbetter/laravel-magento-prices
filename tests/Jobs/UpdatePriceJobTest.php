<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoBasePricesJob;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoSpecialPricesJob;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoTierPricesJob;
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

        Bus::assertBatched(function (PendingBatch $batch): bool {
            /** @var Collection $jobs */
            $jobs = $batch->jobs->map(function (mixed $job): string {
                return get_class($job);
            });

            if ($jobs->contains('JustBetter\MagentoPrices\Jobs\UpdateMagentoBasePricesJob') && $jobs->contains('JustBetter\MagentoPrices\Jobs\UpdateMagentoTierPricesJob') && $jobs->contains('JustBetter\MagentoPrices\Jobs\UpdateMagentoSpecialPricesJob')) {
                return true;
            }

            return false;
        });

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

        Bus::assertBatched(function (PendingBatch $batch) use ($dispatch): bool {
            return $batch->jobs->first() instanceof $dispatch;
        });

        Bus::assertBatched(function (PendingBatch $batch) use ($notDispatch): bool {
            foreach ($notDispatch as $notDispatchJobClass) {
                foreach ($batch->jobs as $job) {
                    if ($job instanceof $notDispatchJobClass) {
                        return false;
                    }
                }
            }

            return true;
        });
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

        Bus::assertBatched(function (PendingBatch $batch): bool {
            foreach ($batch->jobs as $job) {
                if ($job instanceof UpdateMagentoBasePricesJob) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_it_does_not_dispatch_empty_tier(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['tier_prices' => []]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertBatched(function (PendingBatch $batch): bool {
            foreach ($batch->jobs as $job) {
                if ($job instanceof UpdateMagentoTierPricesJob) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_it_does_not_dispatch_empty_special(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['special_prices' => []]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertBatched(function (PendingBatch $batch): bool {
            foreach ($batch->jobs as $job) {
                if ($job instanceof UpdateMagentoSpecialPricesJob) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_it_does_dispatch_empty_tier_to_remove_tier_prices(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['tier_prices' => [], 'has_tier' => true]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertBatched(function (PendingBatch $batch): bool {
            foreach ($batch->jobs as $job) {
                if ($job instanceof UpdateMagentoTierPricesJob) {
                    return true;
                }
            }

            return false;
        });

        $model = MagentoPrice::findBySku('::sku::');
        $this->assertFalse($model->has_tier);
    }

    public function test_it_does_dispatch_empty_special_to_remove_special_prices(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update(['special_prices' => [], 'has_special' => true]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertBatched(function (PendingBatch $batch): bool {
            foreach ($batch->jobs as $job) {
                if ($job instanceof UpdateMagentoSpecialPricesJob) {
                    return true;
                }
            }

            return false;
        });

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

    public function test_it_clears_failure_data_if_success(): void
    {
        MagentoPrice::findBySku('::sku::')
            ->update([
                'fail_count' => 5,
                'last_failed' => now(),
            ]);

        UpdatePriceJob::dispatchSync('::sku::');

        Bus::assertBatched(function (PendingBatch $batch) {
            /* @var \Illuminate\Queue\SerializableClosure $thenCallback */
            [$thenCallback] = $batch->thenCallbacks();

            $thenCallback->getClosure()->call($this);

            $model = MagentoPrice::findBySku('::sku::');

            $this->assertEquals(0, $model->fail_count);
            $this->assertNull($model->last_failed);

            return true;
        });
    }
}
