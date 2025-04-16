<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoPrices\Actions\Utility\FilterTierPrices;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use PHPUnit\Framework\Attributes\Test;

class FilterTierPricesTest extends TestCase
{
    #[Test]
    public function it_filters_tier_prices(): void
    {
        Http::fake([
            'magento/rest/all/V1/customerGroups/search?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => Http::response([
                'items' => [
                    [
                        'id' => 0,
                        'code' => '::group::',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                ],
            ]),
        ])->preventingStrayRequests();

        $tierPrices = [
            [
                'website_id' => 1,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 10,
            ],
            [
                'website_id' => 0,
                'quantity' => 10,
                'customer_group' => '::group::',
                'price' => 8,
            ],
            [
                'website_id' => 1,
                'quantity' => 1,
                'customer_group' => '::non-existing-group::',
                'price' => 8,
            ],
            [
                'website_id' => 2,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 8,
            ],
        ];

        MagentoProduct::query()->create([
            'sku' => '::sku::',
            'data' => [
                'extension_attributes' => [
                    'website_ids' => [1],
                ],
            ],
        ]);

        $action = app(FilterTierPrices::class);

        $filteredPrices = $action->filter('::sku::', $tierPrices);

        $this->assertEquals([
            [
                'website_id' => 1,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 10,
            ],
            [
                'website_id' => 0,
                'quantity' => 10,
                'customer_group' => '::group::',
                'price' => 8,
            ],
        ], $filteredPrices);

    }

    #[Test]
    public function it_does_not_filter_website_ids_without_data(): void
    {
        Http::fake([
            'magento/rest/all/V1/customerGroups/search?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => Http::response([
                'items' => [
                    [
                        'id' => 0,
                        'code' => '::group::',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                ],
            ]),
        ])->preventingStrayRequests();

        $tierPrices = [
            [
                'website_id' => 1,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 10,
            ],
            [
                'website_id' => 1,
                'quantity' => 1,
                'customer_group' => '::non-existing-group::',
                'price' => 8,
            ],
            [
                'website_id' => 2,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 8,
            ],
        ];

        /** @var FilterTierPrices $action */
        $action = app(FilterTierPrices::class);

        $filteredPrices = $action->filter('::sku::', $tierPrices);

        $this->assertEquals([
            [
                'website_id' => 1,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 10,
            ],
            [
                'website_id' => 2,
                'quantity' => 1,
                'customer_group' => '::group::',
                'price' => 8,
            ],
        ], $filteredPrices);

    }
}
