<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateSpecialPrice;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DeleteCurrentSpecialPricesTest extends TestCase
{
    #[Test]
    public function it_removes_special_prices(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/special-price-information' => Http::response([
                [
                    'price',
                ],
            ]),
            'magento/rest/all/V1/products/special-price-delete' => Http::response(),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_special' => true,
            'special_prices' => [],
        ]);

        /** @var UpdateSpecialPrice $action */
        $action = app(UpdateSpecialPrice::class);
        $action->update($model);
        $this->assertFalse($model->refresh()->has_special);
    }

    #[Test]
    public function it_throws_exception_when_special_price_removal_fails(): void
    {
        Http::fake([
            'magento/rest/all/V1/products/special-price-information' => Http::response([
                [
                    'price',
                ],
            ]),
            'magento/rest/all/V1/products/special-price-delete' => Http::response(null, 500),
        ])->preventStrayRequests();

        /** @var Price $model */
        $model = Price::query()->create([
            'sku' => '::sku::',
            'has_special' => true,
            'special_prices' => [],
        ]);

        /** @var UpdateSpecialPrice $action */
        $action = app(UpdateSpecialPrice::class);

        $this->expectException(RequestException::class);

        $action->update($model);
    }
}
