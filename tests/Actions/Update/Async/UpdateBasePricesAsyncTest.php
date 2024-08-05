<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Async;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdateBasePricesAsync;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateBasePricesAsyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Magento::fake();
    }

    #[Test]
    public function it_updates_base_prices_async(): void
    {
        Http::fake([
            'magento/rest/all/async/bulk/V1/products/base-prices' => Http::response([
                'bulk_uuid' => '::uuid::',
                'request_items' => [
                    [
                        'id' => 0,
                        'status' => 'accepted',
                    ],
                    [
                        'id' => 1,
                        'status' => 'accepted',
                    ],
                ],
            ]),
        ])->preventStrayRequests();

        $models = collect([
            Price::query()->create([
                'sku' => '::sku_1::',
                'base_prices' => [
                    [
                        'store_id' => 1,
                        'price' => 10,
                    ],
                ],
            ]),
            Price::query()->create([
                'sku' => '::sku_2::',
                'base_prices' => [
                    [
                        'store_id' => 1,
                        'price' => 10,
                    ],
                    [
                        'store_id' => 2,
                        'price' => 20,
                    ],
                ],
            ]),
            Price::query()->create([
                'sku' => '::sku_3::',
                'base_prices' => [],
            ]),
            Price::query()->create([
                'sku' => '::sku_4::',
                'base_prices' => [],
            ]),
        ]);

        /** @var UpdateBasePricesAsync $action */
        $action = app(UpdateBasePricesAsync::class);
        $action->update($models);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                [
                    'prices' => [
                        [
                            'store_id' => 1,
                            'price' => 10,
                            'sku' => '::sku_1::',
                        ],
                    ],
                ],
                [
                    'prices' => [
                        [
                            'store_id' => 1,
                            'price' => 10,
                            'sku' => '::sku_2::',
                        ],
                        [
                            'store_id' => 2,
                            'price' => 20,
                            'sku' => '::sku_2::',
                        ],
                    ],
                ],
            ];
        });
    }

    #[Test]
    public function it_does_nothing_when_all_prices_are_rejected(): void
    {
        $models = collect([
            Price::query()->create([
                'sku' => '::sku_3::',
                'base_prices' => [],
            ]),
            Price::query()->create([
                'sku' => '::sku_4::',
            ]),
        ]);

        /** @var UpdateBasePricesAsync $action */
        $action = app(UpdateBasePricesAsync::class);
        $action->update($models);

        Http::assertNothingSent();
    }
}
