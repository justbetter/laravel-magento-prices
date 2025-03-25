<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Sync;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdatePrice;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesBasePrice;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesSpecialPrice;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesTierPrice;
use JustBetter\MagentoPrices\Events\UpdatedPriceEvent;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdatePriceTest extends TestCase
{
    #[Test]
    public function it_does_nothing_when_not_in_magento(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock): void {
            $mock->shouldReceive('exists')->with('::sku::')->andReturnFalse();
        });

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var UpdatePrice $action */
        $action = app(UpdatePrice::class);
        $action->update($model);

        $model->refresh();
        $this->assertFalse($model->update);
    }

    #[Test]
    public function it_calls_update_actions(): void
    {
        Event::fake([UpdatedPriceEvent::class]);

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock): void {
            $mock->shouldReceive('exists')->with('::sku::')->andReturnTrue();
        });

        $this->mock(UpdatesBasePrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->andReturnTrue();
        });

        $this->mock(UpdatesTierPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->andReturnTrue();
        });

        $this->mock(UpdatesSpecialPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->andReturnTrue();
        });

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
            'fail_count' => 1,
            'last_failed' => now(),
        ]);

        /** @var UpdatePrice $action */
        $action = app(UpdatePrice::class);
        $action->update($model);

        $model->refresh();

        $this->assertFalse($model->update);
        $this->assertNotNull($model->last_updated);
        $this->assertNull($model->last_failed);
        $this->assertEquals(0, $model->fail_count);

        Event::assertDispatched(UpdatedPriceEvent::class);
    }

    #[Test]
    public function it_registers_failure_when_once_call_fails(): void
    {
        Event::fake([UpdatedPriceEvent::class]);

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock): void {
            $mock->shouldReceive('exists')->with('::sku::')->andReturnTrue();
        });

        $this->mock(UpdatesBasePrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->andReturnTrue();
        });

        $this->mock(UpdatesTierPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->andReturnTrue();
        });

        $this->mock(UpdatesSpecialPrice::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->andReturnFalse();
        });

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
            'fail_count' => 0,
            'last_failed' => null,
        ]);

        /** @var UpdatePrice $action */
        $action = app(UpdatePrice::class);
        $action->update($model);

        $model->refresh();

        $this->assertTrue($model->update);
        $this->assertNotNull($model->last_failed);
        $this->assertEquals(1, $model->fail_count);

        Event::assertNotDispatched(UpdatedPriceEvent::class);
    }
}
