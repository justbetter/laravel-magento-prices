<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateTierPrice;
use JustBetter\MagentoPrices\Actions\Utility\DeleteCurrentTierPrices;
use JustBetter\MagentoPrices\Contracts\Utility\RetrievesCustomerGroups;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class DeleteCurrentTierPricesTest extends TestCase
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
    public function it_removes_tier_prices(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/tier-prices-information' => Http::response([
                [
                    'price' => 100,
                    'price_type' => 'fixed',
                    'website_id' => 0,
                    'sku' => '::sku::',
                    'customer_group' => 'all groups',
                    'quantity' => 1,
                ],
            ]),
            'magento/rest/all/V1/products/tier-prices-delete' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_tier' => true,
            'tier_prices' => [],
        ]);

        /** @var UpdateTierPrice $action */
        $action = app(UpdateTierPrice::class);
        $action->update($model);

        Http::assertSentInOrder([
            fn (Request $request): bool => $request->url() === 'magento/rest/all/V1/products/tier-prices-information'
                && $request->data() === ['skus' => ['::sku::']],

            fn (Request $request): bool => $request->url() === 'magento/rest/all/V1/products/tier-prices-delete'
                && $request->data() === ['prices' => [
                    [
                        'price' => 100,
                        'price_type' => 'fixed',
                        'website_id' => 0,
                        'sku' => '::sku::',
                        'customer_group' => 'all groups',
                        'quantity' => 1,
                    ],
                ]],
        ]);

        $this->assertFalse($model->refresh()->has_tier);
    }

    #[Test]
    public function it_removes_multiple_tier_prices(): void
    {
        $prices = [
            [
                'sku' => '::sku_1::',
                'price' => 10,
                'price_type' => 'fixed',
                'website_id' => 0,
                'customer_group' => 'all groups',
                'quantity' => 1,
            ],
            [
                'sku' => '::sku_2::',
                'price' => 11,
                'price_type' => 'fixed',
                'website_id' => 0,
                'customer_group' => 'all groups',
                'quantity' => 1,
            ],
        ];

        Http::fake([
            'magento/rest/all/V1/products/tier-prices-information' => Http::response($prices),
            'magento/rest/all/V1/products/tier-prices-delete' => Http::response(),
        ])->preventStrayRequests();

        /** @var DeleteCurrentTierPrices $action */
        $action = app(DeleteCurrentTierPrices::class);
        $action->delete(['::sku_1::', '::sku_2::']);

        Http::assertSentInOrder([
            fn (Request $request): bool => $request->url() === 'magento/rest/all/V1/products/tier-prices-information',
            fn (Request $request): bool => $request->url() === 'magento/rest/all/V1/products/tier-prices-delete' && $request->data() === ['prices' => $prices],
        ]);
    }

    #[Test]
    public function it_throws_exception_when_tier_price_removal_fails(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/tier-prices-information' => Http::response([
                [
                    'price' => 100,
                    'price_type' => 'fixed',
                    'website_id' => 0,
                    'sku' => '::sku::',
                    'customer_group' => 'all groups',
                    'quantity' => 1,
                ],
            ]),
            'magento/rest/all/V1/products/tier-prices-delete' => Http::response(null, 500),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_tier' => true,
            'tier_prices' => [],
        ]);

        /** @var UpdateTierPrice $action */
        $action = app(UpdateTierPrice::class);

        $this->expectException(RequestException::class);

        $action->update($model);
    }
}
