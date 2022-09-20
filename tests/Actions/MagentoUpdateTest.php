<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Brick\Money\Money;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoPrices\Actions\UpdateMagentoBasePrice;
use JustBetter\MagentoPrices\Actions\UpdateMagentoTierPrices;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
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

        Http::assertSent(function (Request $request) {
            return $request->data() == [
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
        Http::fake([
            'rest/all/V1/products/tier-prices*' => Http::response([]),
        ]);

        $tierPrice = new TierPriceData('GROUP', Money::of(10, 'EUR'));
        $price = new PriceData('::sku::', collect(), collect([$tierPrice]));

        /** @var UpdateMagentoTierPrices $action */
        $action = app(UpdateMagentoTierPrices::class);
        $action->update($price);

        Http::assertSent(function (Request $request) {
            return $request->data() == [
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
    }
}
