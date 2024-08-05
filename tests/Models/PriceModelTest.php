<?php

namespace JustBetter\MagentoPrices\Tests\Models;

use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PriceModelTest extends TestCase
{
    #[Test]
    public function it_can_register_failures(): void
    {
        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        $model->registerFailure();

        $this->assertNotNull($model->last_failed);
        $this->assertEquals(1, $model->fail_count);
        $this->assertTrue($model->update);
    }

    #[Test]
    public function it_will_set_retrieve_update_too_many_failures(): void
    {
        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'fail_count' => 100,
            'retrieve' => true,
            'update' => true,
        ]);

        $model->registerFailure();

        $this->assertEquals(0, $model->fail_count);
        $this->assertFalse($model->retrieve);
        $this->assertFalse($model->update);
    }

    #[Test]
    public function it_resets_double_state_update(): void
    {
        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'retrieve' => false,
            'update' => false,
        ]);

        $model->retrieve = true;
        $model->update = true;

        $model->save();

        $this->assertTrue($model->retrieve);
        $this->assertFalse($model->update);
    }

    #[Test]
    public function it_resets_double_state_retrieve(): void
    {
        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'retrieve' => false,
            'update' => false,
        ]);

        $model->retrieve = true;
        $model->update = true;

        $model->save();

        $this->assertTrue($model->retrieve);
        $this->assertFalse($model->update);

        $model->update = true;

        $model->save();

        $this->assertFalse($model->retrieve);
        $this->assertTrue($model->update);
    }
}
