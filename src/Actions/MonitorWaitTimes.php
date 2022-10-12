<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoPrices\Contracts\MonitorsWaitTimes;
use JustBetter\MagentoPrices\Events\LongWaitDetectedEvent;
use JustBetter\MagentoPrices\Models\MagentoPrice;

class MonitorWaitTimes implements MonitorsWaitTimes
{
    public function monitor(): void
    {
        $this->monitorRetrievals();
        $this->monitorUpdates();
    }

    protected function monitorRetrievals(): void
    {
        $retrievalsPerMinute = config('magento-prices.retrieve_limit');
        $maxWaitTime = config('magento-prices.monitor.retrieval_max_wait');

        $waitingCount = MagentoPrice::query()
            ->where('sync', '=', true)
            ->where('retrieve', '=', true)
            ->count();

        $wait = $waitingCount / $retrievalsPerMinute;

        if ($wait > $maxWaitTime) {
            event(new LongWaitDetectedEvent('retrieve', $wait));
        }
    }

    protected function monitorUpdates(): void
    {
        $retrievalsPerMinute = config('magento-prices.update_limit');
        $maxWaitTime = config('magento-prices.monitor.update_max_wait');

        $waitingCount = MagentoPrice::query()
            ->where('sync', '=', true)
            ->where('update', '=', true)
            ->count();

        $wait = $waitingCount / $retrievalsPerMinute;

        if ($wait > $maxWaitTime) {
            event(new LongWaitDetectedEvent('update', $wait));
        }
    }

    public static function bind(): void
    {
        app()->singleton(MonitorsWaitTimes::class, static::class);
    }
}
