<?php

namespace JustBetter\MagentoPrices\Listeners;

use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Listeners\BulkOperationStatusListener as BaseBulkOperationStatusListener;
use JustBetter\MagentoAsync\Models\BulkOperation;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Models\Price;

class BulkOperationStatusListener extends BaseBulkOperationStatusListener
{
    protected string $model = Price::class;

    public function execute(BulkOperation $operation): void
    {
        /** @var Price $price */
        $price = $operation->subject;

        if ($operation->status === OperationStatus::Complete) {
            $price->update(['last_updated' => now()]);

            event(new UpdatedPriceEvent($price));

            return;
        }

        activity()
            ->useLog('error')
            ->withProperties([
                'status' => $operation->status->name ?? 'unknown',
                'response' => $operation->response,
            ])
            ->log('Failed to update price');
    }
}
