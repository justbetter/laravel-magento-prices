<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use JustBetter\MagentoPrices\Actions\Utility\CheckTierDuplicates;
use JustBetter\MagentoPrices\Exceptions\DuplicateTierPriceException;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckTierDuplicatesTest extends TestCase
{
    #[Test]
    public function it_passes(): void
    {
        /** @var Price $model */
        $model = Price::query()->create(['sku' => '::sku::']);

        $tierPrices = [
            [
                'store_id' => 1,
                'quantity' => 1,
                'group_id' => 'GENERAL',
                'price' => 10,
            ],
            [
                'store_id' => 1,
                'quantity' => 1,
                'group_id' => 'RETAIL',
                'price' => 10,
            ],
        ];

        /** @var CheckTierDuplicates $action */
        $action = app(CheckTierDuplicates::class);
        $action->check($model, $tierPrices);

        $this->assertTrue(true, 'Passed');
    }

    #[Test]
    public function it_throws_exception(): void
    {
        /** @var Price $model */
        $model = Price::query()->create(['sku' => '::sku::']);

        $tierPrices = [
            [
                'store_id' => 1,
                'quantity' => 1,
                'group_id' => 'GENERAL',
                'price' => 10,
            ],
            [
                'store_id' => 1,
                'quantity' => 1,
                'group_id' => 'GENERAL',
                'price' => 20,
            ],
        ];

        $this->expectException(DuplicateTierPriceException::class);

        /** @var CheckTierDuplicates $action */
        $action = app(CheckTierDuplicates::class);
        $action->check($model, $tierPrices);
    }
}
