<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Async;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdateSpecialPricesAsync;
use JustBetter\MagentoPrices\Contracts\Utility\DeletesCurrentSpecialPrices;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateSpecialPricesAsyncTest extends TestCase
{
    #[Test]
    public function it_updates_special_prices_async(): void
    {
        $this->mock(DeletesCurrentSpecialPrices::class, function(MockInterface $mock): void {
            $mock->shouldReceive('delete')->twice()->andReturn();
        });

        Magento::fake();

        Http::fake([
            'magento/rest/all/async/bulk/V1/products/special-price' => Http::response([
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
                'has_special' => true,
                'special_prices' => [
                    [
                        'store_id' => 1,
                        'price' => 10,
                        'from' => '2024-07-30',
                        'to' => '2024-08-30',
                    ],
                ],
            ]),
            Price::query()->create([
                'sku' => '::sku_2::',
                'has_special' => false,
                'special_prices' => [
                    [
                        'store_id' => 1,
                        'price' => 10,
                        'from' => '2024-07-30',
                        'to' => '2024-08-30',
                    ],
                ],
            ]),
            Price::query()->create([
                'sku' => '::sku_3::',
                'has_special' => true,
                'special_prices' => [],
            ]),
            Price::query()->create([
                'sku' => '::sku_4::',
                'special_prices' => [],
            ]),
        ]);

        /** @var UpdateSpecialPricesAsync $action */
        $action = app(UpdateSpecialPricesAsync::class);
        $action->update($models);

        Http::assertSent(function (Request $request): bool {
            return $request->data() === [
                    [
                        'prices' => [
                            [
                                'store_id' => 1,
                                'price' => 10,
                                'from' => '2024-07-30',
                                'to' => '2024-08-30',
                                'sku' => '::sku_1::',
                            ],
                        ],
                    ],
                    [
                        'prices' => [
                            [
                                'store_id' => 1,
                                'price' => 10,
                                'from' => '2024-07-30',
                                'to' => '2024-08-30',
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
                'special_prices' => [],
            ]),
            Price::query()->create([
                'sku' => '::sku_4::',
            ]),
        ]);

        /** @var UpdateSpecialPricesAsync $action */
        $action = app(UpdateSpecialPricesAsync::class);
        $action->update($models);

        Http::assertNothingSent();
    }
}
