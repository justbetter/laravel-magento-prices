<?php

namespace JustBetter\MagentoPrices\Tests\Actions;

use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JustBetter\MagentoPrices\Actions\ProcessPrice;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\Mocks\CheckMagentoExistenceMock;
use JustBetter\MagentoPrices\Tests\TestCase;

class ProcessPriceTest extends TestCase
{
    use RefreshDatabase;

    protected PriceData $data;

    protected ProcessPrice $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ProcessPrice(new CheckMagentoExistenceMock());

        $basePrices = collect([
            new BasePriceData(Money::of(10, 'EUR'), 0),
        ]);

        $tierPrices = collect([
            new TierPriceData('::group_1::', Money::of(10, 'EUR'), 0),
        ]);

        $this->data = new PriceData('::sku::', $basePrices, $tierPrices);

        MagentoPrice::create([
            'sync' => false,
            'sku' => '::sku::',
            'base_prices' => $basePrices,
            'tier_prices' => $tierPrices,
        ]);
    }

    public function test_it_does_not_set_update_if_no_changes(): void
    {
        $dto = $this->data->getModel()->getData();

        $this->action->process($dto);

        $this->assertEquals(false, $this->data->getModel()->update);
    }

    public function test_it_sets_update(): void
    {
        $this->data->basePrices = collect([
            new BasePriceData(Money::of(11, 'EUR'), 0),
        ]);

        $this->action->process($this->data);

        $this->assertEquals(true, $this->data->getModel()->update);
        $this->assertEquals(false, $this->data->getModel()->retrieve);
    }
}
