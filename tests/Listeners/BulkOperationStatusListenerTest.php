<?php

namespace JustBetter\MagentoPrices\Tests\Listeners;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Models\BulkOperation;
use JustBetter\MagentoAsync\Models\BulkRequest;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Listeners\BulkOperationStatusListener;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BulkOperationStatusListenerTest extends TestCase
{
    #[Test]
    public function it_handles_complete_status(): void
    {
        Event::fake();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => 'sku',
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'magento_connection' => 'default',
            'store_code' => 'store',
            'path' => 'products',
            'bulk_uuid' => '::uuid::',
            'request' => [],
            'response' => [],
        ]);

        /** @var BulkOperation $operation */
        $operation = $request->operations()->create([
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'operation_id' => 0,
            'status' => OperationStatus::Complete,
        ]);

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);

        $listener->execute($operation);

        Event::assertDispatched(UpdatedPriceEvent::class);
        $this->assertNotNull($model->refresh()->last_updated);
    }

    #[Test]
    public function it_handles_failed_status(): void
    {
        Event::fake();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => 'sku',
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'magento_connection' => 'default',
            'store_code' => 'store',
            'path' => 'products',
            'bulk_uuid' => '::uuid::',
            'request' => [],
            'response' => [],
        ]);

        /** @var BulkOperation $operation */
        $operation = $request->operations()->create([
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'operation_id' => 0,
            'status' => OperationStatus::NotRetriablyFailed,
        ]);

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);

        $listener->execute($operation);

        Event::assertNotDispatched(UpdatedPriceEvent::class);
        $this->assertNull($model->refresh()->last_updated);
    }
}
