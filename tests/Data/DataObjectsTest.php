<?php

namespace JustBetter\MagentoPrices\Tests\Data;

use Brick\Money\Context\CustomContext;
use Brick\Money\Money;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Tests\TestCase;

class DataObjectsTest extends TestCase
{
    public function test_base_price_data_constructor_order(): void
    {
        $price = Money::of(10, 'EUR');
        $storeId = 1;

        $data = new BasePriceData($price, $storeId);

        $this->assertEquals($price, $data->getPrice());
        $this->assertEquals($storeId, $data->getStoreId());
    }

    public function test_base_price_price_parse(): void
    {
        $price = Money::of(10,
            config('magento-prices.currency'),
            new CustomContext(config('magento-prices.precision')),
            config('magento-prices.rounding_mode')
        );

        $data = new BasePriceData($price, 0);

        $data->parsePrice(10);

        $this->assertEquals($price, $data->getPrice());
    }

    public function test_base_price_data_setters(): void
    {
        $price = Money::of(10, 'EUR');
        $storeId = 1;

        $data = new BasePriceData($price, $storeId);

        $price = $price->plus(10);

        $data->setPrice($price);
        $data->setStoreId(10);

        $this->assertEquals($price, $data->getPrice());
        $this->assertEquals(10, $data->getStoreId());
    }

    public function test_base_price_to_array(): void
    {
        $price = Money::of(1.12, 'EUR');
        $storeId = 0;

        $data = new BasePriceData($price, $storeId);

        $this->assertEquals([
            'storeId' => 0,
            'price' => 1.12,
        ], $data->toArray());
    }

    public function test_tier_prices_constructor_order(): void
    {
        $storeId = 1;
        $quantity = 1;
        $groupId = '::group::';
        $priceType = 'fixed';
        $price = Money::of(10, 'EUR');

        $data = new TierPriceData($groupId, $price, $quantity, $storeId, $priceType);

        $this->assertEquals($storeId, $data->getStoreId());
        $this->assertEquals($quantity, $data->getQuantity());
        $this->assertEquals($groupId, $data->getGroupId());
        $this->assertEquals($priceType, $data->getPriceType());
        $this->assertEquals($price, $data->getPrice());
    }

    public function test_tier_prices_default_values(): void
    {
        $storeId = 0;
        $quantity = 1;
        $groupId = '::group::';
        $priceType = 'fixed';
        $price = Money::of(10, 'EUR');

        $data = new TierPriceData($groupId, $price);

        $this->assertEquals($storeId, $data->getStoreId());
        $this->assertEquals($quantity, $data->getQuantity());
        $this->assertEquals($groupId, $data->getGroupId());
        $this->assertEquals($priceType, $data->getPriceType());
        $this->assertEquals($price, $data->getPrice());
    }

    public function test_tier_prices_setters(): void
    {
        $groupId = '::group::';
        $price = Money::of(10, 'EUR');

        $data = new TierPriceData($groupId, $price);

        $price = $price->plus(10);

        $data->setStoreId(10);
        $data->setPrice($price);
        $data->setQuantity(10);
        $data->setGroupId('::new_group::');
        $data->setPriceType('::some_type::');

        $this->assertEquals(10, $data->getStoreId());
        $this->assertEquals(10, $data->getQuantity());
        $this->assertEquals('::new_group::', $data->getGroupId());
        $this->assertEquals('::some_type::', $data->getPriceType());
        $this->assertEquals($price, $data->getPrice());
    }

    public function test_tier_prices_identifier(): void
    {
        $groupId = '::group::';
        $price = Money::of(10, 'EUR');

        $data = new TierPriceData($groupId, $price);

        $this->assertEquals('0-1-::group::', $data->getIdentifier());
    }

    public function test_tier_prices_to_array(): void
    {
        $groupId = '::group::';
        $price = Money::of(10, 'EUR');

        $data = new TierPriceData($groupId, $price);

        $this->assertEquals([
            'storeId' => 0,
            'quantity' => 1,
            'price' => 10.00,
            'customer_group' => $groupId,
        ], $data->toArray());
    }

    public function test_special_price(): void
    {
        $from = now()->subMonth();
        $to = now();

        $price = Money::of(10,
            config('magento-prices.currency'),
            new CustomContext(config('magento-prices.precision')),
            config('magento-prices.rounding_mode')
        );

        $specialPrice = new SpecialPriceData($price);

        $specialPrice->setPrice($price);
        $specialPrice->setStoreId(1);
        $specialPrice->setFrom($from);
        $specialPrice->setTo($to);
        $specialPrice->parsePrice(11);

        $this->assertEquals([
            'storeId' => 1,
            'price' => '11.0000',
            'price_from' => $from->toDateTimeString(),
            'price_to' => $to->toDateTimeString(),
        ], $specialPrice->toArray());
    }
}
