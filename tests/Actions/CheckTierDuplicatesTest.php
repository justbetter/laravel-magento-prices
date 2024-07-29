<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Brick\Money\Money;
use JustBetter\MagentoPrices\Actions\Utility\CheckTierDuplicates;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Exceptions\DuplicateTierPriceException;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;

class CheckTierDuplicatesTest extends TestCase
{
    public function test_it_passes(): void
    {
        $action = new CheckTierDuplicates();

        $prices = collect([
            new TierPriceData('GROUP', Money::of(1, 'EUR'), 1, 0),
            new TierPriceData('GROUP', Money::of(1, 'EUR'), 2, 0),
            new TierPriceData('GROUP 2', Money::of(1, 'EUR'), 1, 0),
            new TierPriceData('GROUP', Money::of(1, 'EUR'), 1, 1),
        ]);

        try {
            $action->check('::sku::', $prices);
        } catch (DuplicateTierPriceException $e) {
            $this->assertTrue(false, 'exception thrown');
        }

        $this->assertTrue(true);
    }

    public function test_it_fails(): void
    {
        $action = new CheckTierDuplicates();

        $prices = collect([
            new TierPriceData('GROUP', Money::of(1, 'EUR'), 1, 0),
            new TierPriceData('GROUP', Money::of(1, 'EUR'), 1, 0),
        ]);

        MagentoPrice::query()->create([
            'sku' => '::sku::',
        ]);

        $this->expectException(DuplicateTierPriceException::class);

        $action->check('::sku::', $prices);
    }
}
