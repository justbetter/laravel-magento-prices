<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Retrieval;

use Illuminate\Support\Carbon;
use JustBetter\MagentoPrices\Actions\Retrieval\SavePrice;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SavePriceTest extends TestCase
{
    #[Test]
    public function it_saves_fields(): void
    {
        Carbon::setTestNow('2024-08-05');

        $priceData = PriceData::of([
            'sku' => '::sku::',
            'base_prices' => [
                [
                    'store_id' => 0,
                    'price' => 10,
                ],
            ],
            'tier_prices' => [
                [
                    'website_id' => 0,
                    'customer_group' => 'group_1',
                    'price_type' => 'fixed',
                    'quantity' => 1,
                    'price' => 8,
                ],
            ],
            'special_prices' => [
                [
                    'store_id' => 0,
                    'price' => 5,
                    'price_from' => now()->subWeek()->toDateString(),
                    'price_to' => now()->addWeek()->toDateString(),
                ],
            ],
        ]);

        /** @var SavePrice $action */
        $action = app(SavePrice::class);
        $action->save($priceData, false);

        /** @var Price $model */
        $model = Price::query()->firstWhere('sku', '=', '::sku::');

        $this->assertNotNull($model->base_prices);
        $this->assertNotNull($model->tier_prices);
        $this->assertNotNull($model->special_prices);
        $this->assertEquals([['store_id' => 0, 'price' => 10]], $model->base_prices);
        $this->assertEquals([
            [
                'website_id' => 0,
                'customer_group' => 'group_1',
                'price_type' => 'fixed',
                'quantity' => 1,
                'price' => 8,
            ],
        ], $model->tier_prices);

        $this->assertEquals([
            [
                'store_id' => 0,
                'price' => 5,
                'price_from' => now()->subWeek()->toDateString(),
                'price_to' => now()->addWeek()->toDateString(),
            ],
        ], $model->special_prices);

        $this->assertTrue($model->sync);
        $this->assertFalse($model->retrieve);
        $this->assertTrue($model->update);
        $this->assertNotNull($model->last_retrieved);
        $this->assertEquals('27f10836349f35baf9aa229f963e4ddf', $model->checksum);

    }

    #[Test]
    public function it_does_not_set_update_when_unchanged(): void
    {
        $priceData = PriceData::of([
            'sku' => '::sku::',
            'base_prices' => [
                [
                    'store_id' => 0,
                    'price' => 10,
                ],
            ],
            'tier_prices' => [
                [
                    'website_id' => 0,
                    'customer_group' => 'group_1',
                    'price_type' => 'fixed',
                    'quantity' => 1,
                    'price' => 8,
                ],
            ],
            'special_prices' => [
                [
                    'store_id' => 0,
                    'price' => 5,
                    'price_from' => now()->subWeek()->toDateString(),
                    'price_to' => now()->addWeek()->toDateString(),
                ],
            ],
        ]);

        /** @var SavePrice $action */
        $action = app(SavePrice::class);
        $action->save($priceData, false);

        /** @var Price $model */
        $model = Price::query()->firstWhere('sku', '=', '::sku::');

        $this->assertTrue($model->update);

        $model->update(['update' => false]);

        $action->save($priceData, false);

        $this->assertFalse($model->refresh()->update);
    }

    #[Test]
    public function it_can_force_update(): void
    {
        $priceData = PriceData::of([
            'sku' => '::sku::',
            'base_prices' => [
                [
                    'store_id' => 0,
                    'price' => 10,
                ],
            ],
            'tier_prices' => [
                [
                    'website_id' => 0,
                    'customer_group' => 'group_1',
                    'price_type' => 'fixed',
                    'quantity' => 1,
                    'price' => 8,
                ],
            ],
            'special_prices' => [
                [
                    'store_id' => 0,
                    'price' => 5,
                    'price_from' => now()->subWeek()->toDateString(),
                    'price_to' => now()->addWeek()->toDateString(),
                ],
            ],
        ]);

        /** @var SavePrice $action */
        $action = app(SavePrice::class);
        $action->save($priceData, false);

        /** @var Price $model */
        $model = Price::query()->firstWhere('sku', '=', '::sku::');

        $this->assertTrue($model->update);

        $model->update(['update' => false]);

        $action->save($priceData, true);

        $this->assertTrue($model->refresh()->update);
    }
}
