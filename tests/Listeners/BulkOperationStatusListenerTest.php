<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Models\BulkOperation;
use JustBetter\MagentoAsync\Models\BulkRequest;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Listeners\BulkOperationStatusListener;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class BulkOperationStatusListenerTest extends TestCase
{
    #[Test]
    public function it_handles_complete_status(): void
    {
        Event::fake();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => 'sku',
            'fail_count' => 1,
            'last_failed' => now(),
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'magento_connection' => 'default',
            'method' => 'POST',
            'store_code' => 'store',
            'path' => 'products',
            'bulk_uuid' => '::uuid::',
            'request' => [],
            'response' => [],
        ]);

        /** @var BulkOperation $operation */
        $operation = $request->operations()->create([
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'operation_id' => 0,
            'status' => OperationStatus::Complete,
        ]);

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);

        $listener->execute($operation);

        Event::assertDispatched(UpdatedPriceEvent::class);
        $model->refresh();
        $this->assertInstanceOf(Carbon::class, $model->last_updated);
        $this->assertEquals(0, $model->fail_count);
        $this->assertNotInstanceOf(Carbon::class, $model->last_failed);
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
            'method' => 'POST',
            'store_code' => 'store',
            'path' => 'products',
            'bulk_uuid' => '::uuid::',
            'request' => [],
            'response' => [],
        ]);

        /** @var BulkOperation $operation */
        $operation = $request->operations()->create([
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'operation_id' => 0,
            'status' => OperationStatus::NotRetriablyFailed,
        ]);

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);

        $listener->execute($operation);

        Event::assertNotDispatched(UpdatedPriceEvent::class);
        $this->assertNotInstanceOf(Carbon::class, $model->refresh()->last_updated);
    }
}
