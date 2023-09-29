<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Brick\Money\Money;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoPrices\Actions\UpdateMagentoBasePrice;
use JustBetter\MagentoPrices\Actions\UpdateMagentoTierPrices;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Exceptions\PriceUpdateException;
use JustBetter\MagentoPrices\Jobs\ImportGroupsJob;
use JustBetter\MagentoPrices\Models\MagentoGroup;
use JustBetter\MagentoPrices\Tests\TestCase;

class MagentoUpdateTest extends TestCase
{
    public function test_it_calls_magento_base_price_post(): void
    {
        Http::fake([
            'rest/all/V1/products/base-prices*' => Http::response([]),
        ]);

        $price = new PriceData('::sku::', collect([new BasePriceData(Money::of(10, 'EUR'))]));

        /** @var UpdateMagentoBasePrice $action */
        $action = app(UpdateMagentoBasePrice::class);
        $action->update($price);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                'prices' => [
                    [
                        'sku' => '::sku::',
                        'price' => 10.0,
                        'store_id' => 0,
                    ],
                ],
            ];
        });
    }

    public function test_it_calls_magento_base_price_post_async(): void
    {
        config()->set('magento-prices.async', true);

        Http::fake([
            'rest/all/async/V1/products/base-prices*' => Http::response([]),
        ]);

        $price = new PriceData('::sku::', collect([new BasePriceData(Money::of(10, 'EUR'))]));

        /** @var UpdateMagentoBasePrice $action */
        $action = app(UpdateMagentoBasePrice::class);
        $action->update($price);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                'prices' => [
                    [
                        'sku' => '::sku::',
                        'price' => 10.0,
                        'store_id' => 0,
                    ],
                ],
            ];
        });
    }

    public function test_it_calls_magento_tier_price_post(): void
    {
        Bus::fake([
            ImportGroupsJob::class,
        ]);

        Http::fake([
            'rest/all/V1/products/tier-prices*' => Http::response([]),
        ]);

        MagentoGroup::query()->create([
            'code' => 'GROUP',
            'data' => [],
        ]);

        $price = new PriceData('::sku::', collect(), collect([
            new TierPriceData('GROUP', Money::of(10, 'EUR')),
            new TierPriceData('GROUP2', Money::of(10, 'EUR')),
        ]));

        /** @var UpdateMagentoTierPrices $action */
        $action = app(UpdateMagentoTierPrices::class);
        $action->update($price);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                'prices' => [
                    [
                        'sku' => '::sku::',
                        'price' => 10.0,
                        'website_id' => 0,
                        'quantity' => 1,
                        'customer_group' => 'GROUP',
                        'price_type' => 'fixed',
                    ],
                ],
            ];
        });

        Bus::assertDispatched(ImportGroupsJob::class);
    }

    public function test_it_calls_magento_tier_price_post_async(): void
    {
        config()->set('magento-prices.async', true);

        Bus::fake([
            ImportGroupsJob::class,
        ]);

        Http::fake([
            'rest/all/async/V1/products/tier-prices*' => Http::response([]),
        ]);

        MagentoGroup::query()->create([
            'code' => 'GROUP',
            'data' => [],
        ]);

        $price = new PriceData('::sku::', collect(), collect([
            new TierPriceData('GROUP', Money::of(10, 'EUR')),
            new TierPriceData('GROUP2', Money::of(10, 'EUR')),
        ]));

        /** @var UpdateMagentoTierPrices $action */
        $action = app(UpdateMagentoTierPrices::class);
        $action->update($price);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                'prices' => [
                    [
                        'sku' => '::sku::',
                        'price' => 10.0,
                        'website_id' => 0,
                        'quantity' => 1,
                        'customer_group' => 'GROUP',
                        'price_type' => 'fixed',
                    ],
                ],
            ];
        });

        Bus::assertDispatched(ImportGroupsJob::class);
    }

    public function test_it_can_throw_exceptions_if_groups_are_unavailable(): void
    {
        $this->expectException(PriceUpdateException::class);

        Bus::fake([
            ImportGroupsJob::class,
        ]);

        Http::fake([
            'rest/all/V1/products/tier-prices*' => Http::response([]),
        ]);

        $price = new PriceData('::sku::', collect(), collect([
            new TierPriceData('GROUP', Money::of(10, 'EUR')),
            new TierPriceData('GROUP2', Money::of(10, 'EUR')),
        ]));

        /** @var UpdateMagentoTierPrices $action */
        $action = app(UpdateMagentoTierPrices::class);
        $action->update($price);

        Bus::assertDispatched(ImportGroupsJob::class);
    }
}
