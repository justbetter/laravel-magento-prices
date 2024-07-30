<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Actions\Utility\ImportCustomerGroups;
use JustBetter\MagentoPrices\Models\CustomerGroup;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ImportCustomerGroupsTest extends TestCase
{
    #[Test]
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

        $count = CustomerGroup::query()->count();

        $this->assertEquals(3, $count);
    }

    #[Test]
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

        CustomerGroup::query()->create([
            'code' => 'General',
            'data' => [],
        ]);

        /** @var Price $price */
        $price = Price::query()->create([
            'sku' => '1000',
            'update' => false,
        ]);

        /** @var ImportCustomerGroups $action */
        $action = app(ImportCustomerGroups::class);
        $action->import();

        $count = CustomerGroup::query()->count();

        $this->assertEquals(3, $count);

        $price->refresh();

        $this->assertTrue($price->update);
    }

    #[Test]
    public function it_can_delete_customer_groups(): void
    {
        Magento::fake();

        Http::fake([
            'magento/rest/all/V1/customerGroups/search?searchCriteria%5BpageSize%5D=100&searchCriteria%5BcurrentPage%5D=1' => [
                'items' => [],
            ],
        ]);

        CustomerGroup::query()->create([
            'code' => 'Delete',
            'data' => [],
        ]);

        /** @var Price $price */
        $price = Price::query()->create([
            'sku' => '1000',
            'sync' => false,
            'update' => false,
        ]);

        /** @var ImportCustomerGroups $action */
        $action = app(ImportCustomerGroups::class);
        $action->import();

        $count = CustomerGroup::query()->count();

        $this->assertEquals(0, $count);

        $price->refresh();

        $this->assertTrue($price->sync);
        $this->assertTrue($price->update);
    }
}
