<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Async;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdateTierPricesAsync;
use JustBetter\MagentoPrices\Contracts\Utility\RetrievesCustomerGroups;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateTierPricesAsyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Magento::fake();

        $this->mock(RetrievesCustomerGroups::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retrieve')->andReturn(['GENERAL', 'RETAIL']);
        });
    }

    #[Test]
    public function it_updates_tier_prices_async(): void
    {
        Http::fake([
            'magento/rest/all/async/bulk/V1/products/tier-prices' => Http::response([
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
            ])
        ])->preventStrayRequests();

        $models = collect([
            Price::query()->create([
                'sku' => '::sku_1::',
                'has_tier' => false,
                'tier_prices' => [
                    [
                        'website_id' => 1,
                        'quantity' => 1,
                        'customer_group' => 'GENERAL',
                        'price' => 10,
                    ],
                    [
                        'website_id' => 1,
                        'quantity' => 1,
                        'customer_group' => 'RETAIL',
                        'price' => 8,
                    ],
                ],
            ]),
            Price::query()->create([
                'sku' => '::sku_2::',
                'has_tier' => false,
                'tier_prices' => [
                    [
                        'website_id' => 1,
                        'quantity' => 1,
                        'customer_group' => 'GENERAL',
                        'price' => 10,
                    ],
                    [
                        'website_id' => 1,
                        'quantity' => 1,
                        'customer_group' => 'RETAIL',
                        'price' => 8,
                    ],
                ],
            ]),
            Price::query()->create([
                'sku' => '::sku_3::',
                'has_tier' => false,
                'tier_prices' => [],
            ]),
            Price::query()->create([
                'sku' => '::sku_4::',
                'has_tier' => true,
                'tier_prices' => [],
            ]),
        ]);

        /** @var UpdateTierPricesAsync $action */
        $action = app(UpdateTierPricesAsync::class);
        $action->update($models);

        Http::assertSent(function (Request $request): bool {
            dd($request->data());

            return $request->data() === [
                    [
                        'prices' => [
                            [
                                'website_id' => 1,
                                'quantity' => 1,
                                'customer_group' => 'GENERAL',
                                'price' => 10,
                                'sku' => '::sku_1::',
                            ],
                            [
                                'website_id' => 1,
                                'quantity' => 1,
                                'customer_group' => 'RETAIL',
                                'price' => 8,
                                'sku' => '::sku_1::',
                            ],
                        ],
                    ],
                    [
                        'prices' => [
                            [
                                'website_id' => 1,
                                'quantity' => 1,
                                'customer_group' => 'GENERAL',
                                'price' => 10,
                                'sku' => '::sku_2::',
                            ],
                            [
                                'website_id' => 1,
                                'quantity' => 1,
                                'customer_group' => 'RETAIL',
                                'price' => 8,
                                'sku' => '::sku_2::',
                            ],
                        ],
                    ],
                ];
        });
    }
}
