<?php

namespace JustBetter\MagentoPrices\Tests\Data;

use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;

class PriceDataTest extends TestCase
{
    use RefreshDatabase;

    protected PriceData $data;

    protected function setUp(): void
    {
        parent::setUp();

        $basePrices = collect([
            new BasePriceData(Money::of(10, 'EUR'), 0),
            new BasePriceData(Money::of(11, 'EUR'), 1),
            new BasePriceData(Money::of(12, 'EUR'), 2),
        ]);

        $tierPrices = collect([
            new TierPriceData('::group_1::', Money::of(10, 'EUR'), 0),
            new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
            new TierPriceData('::group_2::', Money::of(5, 'EUR'), 0),
            new TierPriceData('::group_2::', Money::of(2.5, 'EUR'), 10),
        ]);

        $this->data = new PriceData('::sku::', $basePrices, $tierPrices);
    }

    public function test_it_creates_model(): void
    {
        $this->assertDatabaseCount(MagentoPrice::class, 0);

        $this->data->getModel();

        $this->assertDatabaseCount(MagentoPrice::class, 1);
    }

    public function test_it_creates_model_once(): void
    {
        $this->assertDatabaseCount(MagentoPrice::class, 0);

        $this->data->getModel();
        $this->data->getModel();
        $this->data->getModel();

        $this->assertDatabaseCount(MagentoPrice::class, 1);
    }

    public function test_it_creates_array(): void
    {
        $this->assertEquals([
            'base_prices' => collect([
                [
                    'storeId' => 0,
                    'price' => 10.00,
                ],
                [
                    'storeId' => 1,
                    'price' => 11.00,
                ],
                [
                    'storeId' => 2,
                    'price' => 12.00,
                ],
            ]),
            'tier_prices' => collect([
                [
                    'storeId' => 0,
                    'quantity' => 0,
                    'price' => 10.00,
                    'customer_group' => '::group_1::',
                ],
                [
                    'storeId' => 0,
                    'quantity' => 10,
                    'price' => 8.00,
                    'customer_group' => '::group_1::',
                ],
                [
                    'storeId' => 0,
                    'quantity' => 0,
                    'price' => 5.00,
                    'customer_group' => '::group_2::',
                ],
                [
                    'storeId' => 0,
                    'quantity' => 10,
                    'price' => 2.50,
                    'customer_group' => '::group_2::',
                ],
            ]),
            'special_prices' => collect(),
        ], $this->data->toArray());
    }

    public function test_it_gets_magento_base_prices(): void
    {
        $this->assertEquals([
            [
                'sku' => '::sku::',
                'price' => 10.0,
                'store_id' => 0,
            ],
            [
                'sku' => '::sku::',
                'price' => 11.0,
                'store_id' => 1,
            ],
            [
                'sku' => '::sku::',
                'price' => 12.0,
                'store_id' => 2,
            ],
        ], $this->data->getMagentoBasePrices());
    }

    public function test_it_gets_magento_tier_prices(): void
    {
        $this->assertEquals([
            [
                'sku' => '::sku::',
                'price' => 10.0,
                'website_id' => 0,
                'quantity' => 1,
                'customer_group' => '::group_1::',
                'price_type' => 'fixed',
            ],
            [
                'sku' => '::sku::',
                'price' => 8.0,
                'website_id' => 0,
                'quantity' => 10,
                'customer_group' => '::group_1::',
                'price_type' => 'fixed',
            ],
            [
                'sku' => '::sku::',
                'price' => 5.0,
                'website_id' => 0,
                'quantity' => 1,
                'customer_group' => '::group_2::',
                'price_type' => 'fixed',
            ],
            [
                'sku' => '::sku::',
                'price' => 2.5,
                'website_id' => 0,
                'quantity' => 10,
                'customer_group' => '::group_2::',
                'price_type' => 'fixed',
            ],
        ], $this->data->getMagentoTierPrices());
    }

    public function test_it_compares_base_price_data(): void
    {
        $newData = clone $this->data;

        $this->assertTrue($this->data->equals($newData));

        /* @phpstan-ignore-next-line */
        $newData->basePrices = new Collection([
            new TierPriceData('::group_1::', Money::of(10, 'EUR'), 0),
        ]);

        $this->assertFalse($this->data->equals($newData));
    }

    public function test_it_compares_base_tier_data(): void
    {
        $newData = clone $this->data;

        $this->assertTrue($this->data->equals($newData));

        /* @phpstan-ignore-next-line */
        $newData->tierPrices = new Collection([
            new BasePriceData(Money::of(10, 'EUR'), 0),
        ]);

        $this->assertFalse($this->data->equals($newData));
    }
}
