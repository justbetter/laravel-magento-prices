<?php

namespace JustBetter\MagentoPrices\Helpers;

use Brick\Money\Context\CustomContext;
use Brick\Money\Money;

class MoneyHelper
{
    public function getMoney(mixed $amount, string $method = 'of'): Money
    {
        return Money::$method(
            $amount,
            config('magento-prices.currency'),
            new CustomContext(config('magento-prices.precision')),
            config('magento-prices.rounding_mode')
        );
    }
}
