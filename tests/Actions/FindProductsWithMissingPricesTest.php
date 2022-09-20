<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoPrices\Actions\FindProductsWithMissingPrices;
use JustBetter\MagentoPrices\Tests\TestCase;

class FindProductsWithMissingPricesTest extends TestCase
{
    public function test_it_finds_products(): void
    {
        Http::fake([
            '*' => Http::response([
                'items' => [
                    [
                        'sku' => '::sku_1::',
                        'price' => 10,
                        'type_id' => 'simple',
                    ],
                    [
                        'sku' => '::sku_2::',
                        'price' => 0,
                        'type_id' => 'simple',
                    ],
                    [
                        'sku' => '::sku_3::',
                        'type_id' => 'simple',
                    ],
                ],
            ]),
        ]);

        /** @var FindProductsWithMissingPrices $action */
        $action = app(FindProductsWithMissingPrices::class);

        $prices = $action->retrieve()->all();

        $this->assertEquals(['::sku_2::', '::sku_3::'], $prices);
    }
}
