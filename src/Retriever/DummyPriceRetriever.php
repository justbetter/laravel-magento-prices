<?php

namespace JustBetter\MagentoPrices\Retriever;

use Brick\Money\Money;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;

class DummyPriceRetriever extends PriceRetriever
{
    /** {@inheritDoc} */
    public function retrieve(string $sku): ?PriceData
    {
        $basePrice = new BasePriceData(
            Money::of(random_int(1, 100), config('magento-prices.currency'))
        );

        return new PriceData($sku, collect([$basePrice]));
    }
}
