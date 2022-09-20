<?php

namespace JustBetter\MagentoPrices\Tests\Helpers;

use Brick\Math\RoundingMode;
use JustBetter\MagentoPrices\Helpers\MoneyHelper;
use JustBetter\MagentoPrices\Tests\TestCase;

class MoneyHelperTest extends TestCase
{
    /** @dataProvider provider */
    public function test_it_creates_money(float $amount, string $method, ?float $expectedAmount = null): void
    {
        config()->set('laravel-magento-prices.currency', 'EUR');
        config()->set('laravel-magento-prices.precision', 4);
        config()->set('laravel-magento-prices.rounding_mode', RoundingMode::HALF_UP);

        /** @var MoneyHelper $helper */
        $helper = app(MoneyHelper::class);

        $money = $helper->getMoney($amount, $method);

        $this->assertEquals($expectedAmount ?? $amount, $money->getAmount()->toFloat());
    }

    public function provider(): array
    {
        return [
            [
                'amount' => 10,
                'method' => 'of',
            ],
            [
                'amount' => 1000,
                'method' => 'ofMinor',
                'expectedAmount' => 10,
            ],
            [
                'amount' => 1.1234,
                'method' => 'of',
            ],
            [
                'amount' => 112,
                'method' => 'ofMinor',
                'expectedAmount' => 1.12,
            ],
        ];
    }
}
