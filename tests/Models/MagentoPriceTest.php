<?php

namespace JustBetter\MagentoPrices\Tests\Models;

use Carbon\Carbon;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;

class MagentoPriceTest extends TestCase
{
    /** @dataProvider specialPriceProvider */
    public function test_special_price_changed(array $original, array $updated, bool $expectChanged): void
    {
        Carbon::setTestNow(Carbon::createFromDate(2022, 06, 24));

        $model = new MagentoPrice();

        $model->setAttribute('special_prices', $original);

        $model->syncOriginal();

        $model->setAttribute('special_prices', $updated);

        $this->assertEquals($expectChanged, $model->specialPriceChanged());
    }

    public function specialPriceProvider(): array
    {
        return [
            'No change' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'updated' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'expected_change' => false,
            ],

            'Store id changed' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'updated' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 0,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'expected_change' => true,
            ],

            'Price changed' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'updated' => [
                    [
                        'price' => '26.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'expected_change' => true,
            ],

            'Date updated 1 day' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-23 00:00:00',
                        'price_from' => '2022-06-23 00:00:00',
                    ],
                ],
                'updated' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                ],
                'expected_change' => false,
            ],

            'Date expired' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2022-06-23 00:00:00',
                        'price_from' => '2021-06-23 00:00:00',
                    ],
                ],
                'updated' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                ],
                'expected_change' => true,
            ],

            'Original empty' => [
                'original' => [],
                'updated' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                ],
                'expected_change' => true,
            ],

            'Updated empty' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                ],
                'updated' => [],
                'expected_change' => true,
            ],

            'Multiple one changed' => [
                'original' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                    [
                        'price' => '26.0000',
                        'storeId' => 2,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                ],
                'updated' => [
                    [
                        'price' => '25.0000',
                        'storeId' => 1,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                    [
                        'price' => '24.0000',
                        'storeId' => 2,
                        'price_to' => '2023-06-24 00:00:00',
                        'price_from' => '2022-06-24 00:00:00',
                    ],
                ],
                'expected_change' => true,
            ],
        ];
    }

    public function test_it_gets_base_prices(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'base_prices' => [['price' => 10, 'storeId' => 1]],
        ]);

        $model = MagentoPrice::findBySku('::sku::');

        $basePrices = $model->base_prices;
        /** @var BasePriceData $basePrice */
        $basePrice = $basePrices->first();

        $this->assertCount(1, $basePrices);
        $this->assertEquals(1, $basePrice->storeId);
        $this->assertEquals(10, $basePrice->price->getAmount()->toInt());
    }

    public function test_it_gets_tier_prices(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'tier_prices' => [['price' => 10, 'storeId' => 1, 'customer_group' => 'ALL GROUPS', 'quantity' => 1]],
        ]);

        $model = MagentoPrice::findBySku('::sku::');

        $tierPrices = $model->tier_prices;
        /** @var TierPriceData $tierPrice */
        $tierPrice = $tierPrices->first();

        $this->assertCount(1, $tierPrices);
        $this->assertEquals(1, $tierPrice->storeId);
        $this->assertEquals(1, $tierPrice->quantity);
        $this->assertEquals('ALL GROUPS', $tierPrice->groupId);
        $this->assertEquals(10, $tierPrice->price->getAmount()->toInt());
    }

    public function test_it_gets_special_prices(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'special_prices' => [['price' => 10, 'storeId' => 1, 'price_from' => null, 'price_to' => null]],
        ]);

        $model = MagentoPrice::findBySku('::sku::');

        $specialPrices = $model->special_prices;
        /** @var SpecialPriceData $specialPrice */
        $specialPrice = $specialPrices->first();

        $this->assertCount(1, $specialPrices);
        $this->assertEquals(1, $specialPrice->storeId);
        $this->assertEquals(10, $specialPrice->price->getAmount()->toInt());
    }

    public function test_it_gets_single_base_price(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'base_prices' => ['price' => 10, 'storeId' => 1],
        ]);

        $model = MagentoPrice::findBySku('::sku::');

        $basePrices = $model->base_prices;
        /** @var BasePriceData $basePrice */
        $basePrice = $basePrices->first();

        $this->assertCount(1, $basePrices);
        $this->assertEquals(1, $basePrice->storeId);
        $this->assertEquals(10, $basePrice->price->getAmount()->toInt());
    }

    public function test_it_gets_single_tier_price(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'tier_prices' => ['price' => 10, 'storeId' => 1, 'customer_group' => 'ALL GROUPS', 'quantity' => 1],
        ]);

        $model = MagentoPrice::findBySku('::sku::');

        $tierPrices = $model->tier_prices;
        /** @var TierPriceData $tierPrice */
        $tierPrice = $tierPrices->first();

        $this->assertCount(1, $tierPrices);
        $this->assertEquals(1, $tierPrice->storeId);
        $this->assertEquals(1, $tierPrice->quantity);
        $this->assertEquals('ALL GROUPS', $tierPrice->groupId);
        $this->assertEquals(10, $tierPrice->price->getAmount()->toInt());
    }

    public function test_it_gets_single_special_price(): void
    {
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'special_prices' => ['price' => 10, 'storeId' => 1, 'price_from' => null, 'price_to' => null],
        ]);

        $model = MagentoPrice::findBySku('::sku::');

        $specialPrices = $model->special_prices;
        /** @var SpecialPriceData $specialPrice */
        $specialPrice = $specialPrices->first();

        $this->assertCount(1, $specialPrices);
        $this->assertEquals(1, $specialPrice->storeId);
        $this->assertEquals(10, $specialPrice->price->getAmount()->toInt());
    }

    public function test_it_fails_when_registering_too_much_errors(): void
    {
        config()->set('magento-prices.fail_count', 3);
        MagentoPrice::query()->create([
            'sku' => '::sku::',
            'fail_count' => 3,
        ]);

        $model = MagentoPrice::findBySku('::sku::');
        $model->registerError();

        $this->assertFalse($model->update);
        $this->assertFalse($model->retrieve);
        $this->assertEquals(0, $model->fail_count);
    }
}
