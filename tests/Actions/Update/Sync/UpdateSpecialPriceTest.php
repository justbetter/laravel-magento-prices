<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Sync;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateSpecialPrice;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateSpecialPriceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Magento::fake();
    }

    #[Test]
    public function it_updates_special_price(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/special-price' => Http::response(),
            'magento/rest/all/V1/products/special-price-information' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_special' => false,
            'special_prices' => [
                [
                    'store_id' => 1,
                    'price' => 10,
                    'from' => '2024-07-30',
                    'to' => '2024-08-30',
                ],
            ],
        ]);

        /** @var UpdateSpecialPrice $action */
        $action = app(UpdateSpecialPrice::class);
        $this->assertTrue($action->update($model));
        $this->assertTrue($model->refresh()->has_special);
    }

    #[Test]
    public function it_returns_false_on_failure(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/special-price' => Http::response(null, 500),
            'magento/rest/all/V1/products/special-price-information' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_special' => true,
            'special_prices' => [
                [
                    'store_id' => 1,
                    'price' => 10,
                    'from' => '2024-07-30',
                    'to' => '2024-08-30',
                ],
            ],
        ]);

        /** @var UpdateSpecialPrice $action */
        $action = app(UpdateSpecialPrice::class);
        $this->assertFalse($action->update($model));
    }

    #[Test]
    public function it_removes_special_prices(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/special-price-information' => Http::response([
                [
                    'price',
                ],
            ]),
            'magento/rest/all/V1/products/special-price-delete' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_special' => true,
            'special_prices' => [],
        ]);

        /** @var UpdateSpecialPrice $action */
        $action = app(UpdateSpecialPrice::class);
        $action->update($model);
        $this->assertFalse($model->refresh()->has_special);
    }


    #[Test]
    public function it_only_removes_special_prices_once(): void
    {
        Http::fake()->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_special' => false,
            'special_prices' => [],
        ]);

        /** @var UpdateSpecialPrice $action */
        $action = app(UpdateSpecialPrice::class);
        $this->assertTrue($action->update($model));

        Http::assertNothingSent();
    }
}
