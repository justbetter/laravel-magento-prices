<?php

namespace JustBetter\MagentoPrices\Tests\Fakes;

use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Repository\Repository;

class FakeRepository extends Repository
{
    public function retrieve(string $sku): ?PriceData
    {
        return PriceData::of([
            'sku' => $sku,
            'base_prices' => [
                [
                    'store_id' => 0,
                    'price' => 10,
                ],
                [
                    'store_id' => 2,
                    'price' => 19,
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
                [
                    'website_id' => 0,
                    'customer_group' => '4040',
                    'price_type' => 'group_2',
                    'quantity' => 1,
                    'price' => 7,
                ],
            ],
            'special_prices' => [
                [
                    'store_id' => 0,
                    'price' => 5,
                    'price_from' => now()->subWeek()->toDateTimeString(),
                    'price_to' => now()->addWeek()->toDateTimeString(),
                ],
            ],
        ]);
    }
}
