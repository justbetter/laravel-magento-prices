<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Iterator;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Models\BulkOperation;
use JustBetter\MagentoAsync\Models\BulkRequest;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Listeners\BulkOperationStatusListener;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class BulkOperationStatusListenerTest extends TestCase
{
    #[Test]
    #[DataProvider('complete_cases')]
    public function it_handles_complete_status(bool $changedAfterOperation, bool $expectedUpdate): void
    {
        Event::fake();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => 'sku',
            'update' => true,
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

        if ($changedAfterOperation) {
            Price::query()->whereKey($model->id)->update(['updated_at' => now()->addMinute()]);
            $model->refresh();
        }

        /** @var BulkOperationStatusListener $listener */
        $listener = app(BulkOperationStatusListener::class);

        $listener->execute($operation);

        Event::assertDispatched(UpdatedPriceEvent::class);
        $model->refresh();
        $this->assertInstanceOf(Carbon::class, $model->last_updated);
        $this->assertSame(0, $model->fail_count);
        $this->assertNotInstanceOf(Carbon::class, $model->last_failed);
        $this->assertSame($expectedUpdate, $model->update);
    }

    public static function complete_cases(): Iterator
    {
        yield 'unchanged' => [false, false];
        yield 'changed after operation' => [true, true];
    }

    #[Test]
    #[DataProvider('failed_cases')]
    public function it_handles_failed_status(int $failCount, bool $expectedUpdate): void
    {
        Event::fake();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => 'sku',
            'sync' => true,
            'update' => true,
            'fail_count' => $failCount,
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
        $model->refresh();
        $this->assertNotInstanceOf(Carbon::class, $model->last_updated);
        $this->assertInstanceOf(Carbon::class, $model->last_failed);
        $this->assertSame($expectedUpdate, $model->update);
        $this->assertSame($expectedUpdate, $model->sync);
    }

    public static function failed_cases(): Iterator
    {
        yield 'retry' => [0, true];
        yield 'fail limit reached' => [2, false];
    }
}
