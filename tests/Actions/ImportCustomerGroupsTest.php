<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Utility\ImportCustomerGroups;
use JustBetter\MagentoPrices\Models\MagentoCustomerGroup;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;

class ImportCustomerGroupsTest extends TestCase
{
    /** @test */
    public function it_can_import_customer_groups(): void
    {
        Magento::fake();

        Http::fake([
            'magento/rest/all/V1/customerGroups/search?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => [
                'items' => [
                    [
                        'id' => 0,
                        'code' => 'NOT LOGGED IN',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                    [
                        'id' => 1,
                        'code' => 'General',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                    [
                        'id' => 2,
                        'code' => 'Test',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                ],
            ],
        ]);

        /** @var ImportCustomerGroups $action */
        $action = app(ImportCustomerGroups::class);
        $action->import();

        $count = MagentoCustomerGroup::query()->count();

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_can_add_customer_groups(): void
    {
        Magento::fake();

        Http::fake([
            'magento/rest/all/V1/customerGroups/search?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => [
                'items' => [
                    [
                        'id' => 0,
                        'code' => 'NOT LOGGED IN',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                    [
                        'id' => 1,
                        'code' => 'General',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                    [
                        'id' => 2,
                        'code' => 'Test',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Consumers incl. VAT',
                    ],
                ],
            ],
        ]);

        MagentoCustomerGroup::query()->create([
            'code' => 'General',
            'data' => [],
        ]);

        /** @var MagentoPrice $price */
        $price = MagentoPrice::query()->create([
            'sku' => '1000',
            'update' => false,
        ]);

        /** @var ImportCustomerGroups $action */
        $action = app(ImportCustomerGroups::class);
        $action->import();

        $count = MagentoCustomerGroup::query()->count();

        $this->assertEquals(3, $count);

        $price->refresh();

        $this->assertTrue($price->update);
    }

    /** @test */
    public function it_can_delete_customer_groups(): void
    {
        Magento::fake();

        Http::fake([
            'magento/rest/all/V1/customerGroups/search?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => [
                'items' => [],
            ],
        ]);

        MagentoCustomerGroup::query()->create([
            'code' => 'Delete',
            'data' => [],
        ]);

        /** @var MagentoPrice $price */
        $price = MagentoPrice::query()->create([
            'sku' => '1000',
            'sync' => false,
            'update' => false,
        ]);

        /** @var ImportCustomerGroups $action */
        $action = app(ImportCustomerGroups::class);
        $action->import();

        $count = MagentoCustomerGroup::query()->count();

        $this->assertEquals(0, $count);

        $price->refresh();

        $this->assertTrue($price->sync);
        $this->assertTrue($price->update);
    }
}
