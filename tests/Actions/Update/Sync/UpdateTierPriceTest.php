<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Sync;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateTierPrice;
use JustBetter\MagentoPrices\Contracts\Utility\RetrievesCustomerGroups;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateTierPriceTest extends TestCase
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
    public function it_updates_tier_price(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/tier-prices' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
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
        ]);

        /** @var UpdateTierPrice $action */
        $action = app(UpdateTierPrice::class);
        $this->assertTrue($action->update($model));
    }

    #[Test]
    public function it_returns_false_on_failure(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/tier-prices' => Http::response(null, 500),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
        ]);

        /** @var UpdateTierPrice $action */
        $action = app(UpdateTierPrice::class);
        $this->assertFalse($action->update($model));
    }

    #[Test]
    public function it_removes_tier_prices(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/tier-prices' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'tier_prices' => [],
        ]);

        /** @var UpdateTierPrice $action */
        $action = app(UpdateTierPrice::class);
        $this->assertTrue($action->update($model));
    }

    #[Test]
    public function it_only_removes_tier_prices_once(): void
    {
        Http::fake()->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'tier_prices' => [],
        ]);

        /** @var UpdateTierPrice $action */
        $action = app(UpdateTierPrice::class);
        $this->assertTrue($action->update($model));

        Http::assertNothingSent();;
    }
}
