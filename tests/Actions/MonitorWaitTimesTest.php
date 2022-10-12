<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoPrices\Actions\MonitorWaitTimes;
use JustBetter\MagentoPrices\Events\LongWaitDetected;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;

class MonitorWaitTimesTest extends TestCase
{
    public function test_retrieve_wait_times_dispatches_event(): void
    {
        Event::fake();

        config()->set('magento-prices.retrieve_limit', 10);
        config()->set('magento-prices.monitor.retrieval_max_wait', 5);

        for ($i = 0; $i < 100; $i++) {
            MagentoPrice::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => true,
                'update' => false,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertDispatched(LongWaitDetected::class, function (LongWaitDetected $event) {
            return $event->type === 'retrieve' && $event->wait === 10;
        });
    }

    public function test_retrieve_wait_times_does_not_dispatch_event(): void
    {
        Event::fake();

        config()->set('magento-prices.retrieve_limit', 10);
        config()->set('magento-prices.monitor.retrieval_max_wait', 10);

        for ($i = 0; $i < 100; $i++) {
            MagentoPrice::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => true,
                'update' => false,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertNotDispatched(LongWaitDetected::class);
    }

    public function test_update_wait_times_dispatches_event(): void
    {
        Event::fake();

        config()->set('magento-prices.update_limit', 10);
        config()->set('magento-prices.monitor.update_max_wait', 5);

        for ($i = 0; $i < 100; $i++) {
            MagentoPrice::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => false,
                'update' => true,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertDispatched(LongWaitDetected::class, function (LongWaitDetected $event) {
            return $event->type === 'update' && $event->wait === 10;
        });
    }

    public function test_update_wait_times_does_not_dispatch_event(): void
    {
        Event::fake();

        config()->set('magento-prices.update_limit', 10);
        config()->set('magento-prices.monitor.update_max_wait', 10);

        for ($i = 0; $i < 100; $i++) {
            MagentoPrice::query()->create([
                'sku' => $i,
                'sync' => true,
                'retrieve' => false,
                'update' => true,
            ]);
        }

        /** @var MonitorWaitTimes $action */
        $action = app(MonitorWaitTimes::class);

        $action->monitor();

        Event::assertNotDispatched(LongWaitDetected::class);
    }
}
