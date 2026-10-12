<?php

declare(strict_types=1);

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
            $attributes = [
                'last_updated' => now(),
                'last_failed' => null,
                'fail_count' => 0,
            ];

            // A change saved while this operation was open still has to be sent.
            if ($price->updated_at <= $operation->created_at) {
                $attributes['update'] = false;
            }

            $price->update($attributes);

            event(new UpdatedPriceEvent($price));

            return;
        }

        $price->registerFailure();

        if ($price->sync) {
            $price->update([
                'update' => true,
            ]);
        }

        activity()
            ->useLog('error')
            ->on($price)
            ->withProperties([
                'status' => $operation->status->name ?? 'unknown',
                'response' => $operation->response,
            ])
            ->log('Failed to update price');
    }
}
