<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Brick\Money\Money;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoPrices\Actions\UpdateMagentoSpecialPrices;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Tests\TestCase;

class MagentoSpecialPriceUpdateTest extends TestCase
{
    public function test_it_calls_magento_special_price_post(): void
    {
        Http::fake([
            'rest/all/V1/products/special-price-information' => Http::response([
                '::response::',
            ]),
            'rest/all/V1/products/special-price-delete' => Http::response(),
            'rest/all/V1/products/special-price' => Http::response(),
        ]);

        /** @var UpdateMagentoSpecialPrices $action */
        $action = app(UpdateMagentoSpecialPrices::class);

        $from = now()->subDays(30);
        $to = now();

        $specialPrices = collect([
            new SpecialPriceData(Money::of(1, 'EUR'), 0, $from, $to),
        ]);

        $price = new PriceData('::sku::', collect(), collect(), $specialPrices);

        $action->update($price);

        Http::assertSentInOrder([
            fn (Request $request) => $request->data() == ['skus' => ['::sku::']],
            fn (Request $request) => $request->data() == ['prices' => ['::response::']],
            fn (Request $request) => $request->data() == [
                'prices' => [
                    [
                        'sku' => '::sku::',
                        'price' => 1.0,
                        'store_id' => 0,
                        'price_from' => $from->toDateTimeString(),
                        'price_to' => $to->toDateTimeString(),
                    ],
                ],
            ],
        ]);
    }

    public function test_it_calls_magento_special_price_post_async(): void
    {
        config()->set('magento-prices.async', true);

        Http::fake([
            'rest/all/V1/products/special-price-information' => Http::response([
                '::response::',
            ]),
            'rest/all/V1/products/special-price-delete' => Http::response(),
            'rest/all/async/V1/products/special-price' => Http::response(),
        ]);

        /** @var UpdateMagentoSpecialPrices $action */
        $action = app(UpdateMagentoSpecialPrices::class);

        $from = now()->subDays(30);
        $to = now();

        $specialPrices = collect([
            new SpecialPriceData(Money::of(1, 'EUR'), 0, $from, $to),
        ]);

        $price = new PriceData('::sku::', collect(), collect(), $specialPrices);

        $action->update($price);

        Http::assertSentInOrder([
            fn (Request $request) => $request->data() == ['skus' => ['::sku::']],
            fn (Request $request) => $request->data() == ['prices' => ['::response::']],
            fn (Request $request) => $request->data() == [
                    'prices' => [
                        [
                            'sku' => '::sku::',
                            'price' => 1.0,
                            'store_id' => 0,
                            'price_from' => $from->toDateTimeString(),
                            'price_to' => $to->toDateTimeString(),
                        ],
                    ],
                ],
        ]);
    }
}
