<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Sync;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateBasePrice;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateBasePriceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Magento::fake();
    }

    #[Test]
    public function it_updates_base_price(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/base-prices' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_base' => false,
            'base_prices' => [
                [
                    'store_id' => 1,
                    'price' => 10,
                ],
            ],
        ]);

        /** @var UpdateBasePrice $action */
        $action = app(UpdateBasePrice::class);
        $this->assertTrue($action->update($model));
        $this->assertTrue($model->refresh()->has_base);
    }

    #[Test]
    public function it_returns_false_on_failure(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/base-prices' => Http::response(null, 500),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_base' => true,
            'base_prices' => [],
        ]);

        /** @var UpdateBasePrice $action */
        $action = app(UpdateBasePrice::class);
        $this->assertFalse($action->update($model));
    }

    #[Test]
    public function it_removes_base_prices(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/base-prices' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_base' => true,
            'base_prices' => [],
        ]);

        /** @var UpdateBasePrice $action */
        $action = app(UpdateBasePrice::class);
        $this->assertTrue($action->update($model));
        $this->assertFalse($model->refresh()->has_base);
    }

    #[Test]
    public function it_only_removes_base_prices_once(): void
    {
        Http::fake()->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_base' => false,
            'base_prices' => [],
        ]);

        /** @var UpdateBasePrice $action */
        $action = app(UpdateBasePrice::class);
        $this->assertTrue($action->update($model));

        Http::assertNothingSent();;
    }
}
