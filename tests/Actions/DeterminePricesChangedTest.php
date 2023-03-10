<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Brick\Money\Money;
use Carbon\Carbon;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Tests\TestCase;

class DeterminePricesChangedTest extends TestCase
{
    /** @dataProvider dataProvider */
    public function test_it_checks_equals(PriceData $a, PriceData $b, bool $equals): void
    {
        Carbon::setTestNow(Carbon::parse('2023-02-20 00:00:00'));

        $this->assertEquals(
            $equals,
            $a->equals($b)
        );
    }

    public static function dataProvider(): array
    {
        return [
            'Unchanged' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1),
                        new TierPriceData('::group_2::', Money::of(7, 'EUR'), 20, 1),
                    ]),
                    collect([
                        new SpecialPriceData(Money::of(6, 'EUR'), 2, now()->subDay(), now()->addDay()),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1),
                        new TierPriceData('::group_2::', Money::of(7, 'EUR'), 20, 1),
                    ]),
                    collect([
                        new SpecialPriceData(Money::of(6, 'EUR'), 2, now()->subDay(), now()->addDay()),
                    ])
                ),
                'equals' => true,
            ],
            'Base added' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                        new BasePriceData(Money::of(111, 'EUR'), 1),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ])
                ),
                'equals' => false,
            ],
            'Base added, other store id' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                        new BasePriceData(Money::of(111, 'EUR'), 1),
                    ]),
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                        new BasePriceData(Money::of(10, 'EUR'), 2),
                    ]),
                ),
                'equals' => false,
            ],
            'Base changed' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(11, 'EUR')),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ])
                ),
                'equals' => false,
            ],
            'Tier removed' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1),
                        new TierPriceData('::group_2::', Money::of(7, 'EUR'), 20, 1),
                    ]),
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_2::', Money::of(7, 'EUR'), 20, 1),
                    ]),
                ),
                'equals' => false,
            ],
            'Tier changed' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1),
                        new TierPriceData('::group_2::', Money::of(7, 'EUR'), 20, 1),
                    ]),
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1),
                        new TierPriceData('::group_2::', Money::of(6, 'EUR'), 20, 1),
                    ]),
                ),
                'equals' => false,
            ],
            'Tier added, same count' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1),
                        new TierPriceData('::group_2::', Money::of(7, 'EUR'), 20, 1),
                    ]),
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect([
                        new TierPriceData('::group_1::', Money::of(9, 'EUR')),
                        new TierPriceData('::group_1::', Money::of(8, 'EUR'), 10),
                        new TierPriceData('::group_2::', Money::of(8, 'EUR'), 1, 2),
                        new TierPriceData('::group_2::', Money::of(6, 'EUR'), 20, 1),
                    ]),
                ),
                'equals' => false,
            ],
            'Special changed' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(5, 'EUR'), 2, now()->subDay(), now()->addDay()),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(6, 'EUR'), 2, now()->subDay(), now()->addDay()),
                    ])
                ),
                'equals' => false,
            ],
            'Special date' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(5, 'EUR'), 2, now()->subDay(), now()->addDay()),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(5, 'EUR'), 2, now()->subDay(), now()->addWeek()),
                    ])
                ),
                'equals' => false,
            ],
            'Special added' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(5, 'EUR'), 2, now()->subDay(), now()->addDay()),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(6, 'EUR'), 2, now()->subDay(), now()->addDay()),
                        new SpecialPriceData(Money::of(6, 'EUR'), 1, now()->subDay(), now()->addDay()),
                    ])
                ),
                'equals' => false,
            ],
            'Special added, other store' => [
                'a' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(6, 'EUR'), 2, now()->subDay(), now()->addDay()),
                        new SpecialPriceData(Money::of(6, 'EUR'), 1, now()->subDay(), now()->addDay()),
                    ])
                ),
                'b' => new PriceData(
                    '::sku::',
                    collect([
                        new BasePriceData(Money::of(10, 'EUR')),
                    ]),
                    collect(),
                    collect([
                        new SpecialPriceData(Money::of(6, 'EUR'), 2, now()->subDay(), now()->addDay()),
                        new SpecialPriceData(Money::of(6, 'EUR'), 3, now()->subDay(), now()->addDay()),
                    ])
                ),
                'equals' => false,
            ],
        ];
    }
}
