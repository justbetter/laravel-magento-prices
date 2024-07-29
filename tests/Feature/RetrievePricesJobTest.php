<?php

namespace JustBetter\MagentoPrices\Tests\Feature;

use JustBetter\MagentoPrices\Commands\Retrieval\RetrievePriceCommand;
use JustBetter\MagentoPrices\Models\MagentoPrice;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use Mockery\MockInterface;

class RetrievePricesJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')
                ->andReturnTrue();
        });
    }

    public function test_it_retrieves_all(): void
    {
        $this->artisan(RetrievePriceCommand::class);

        $prices = MagentoPrice::all();

        $this->assertCount(2, $prices);
    }

    public function test_it_retrieves_by_date(): void
    {
        $this->artisan(RetrievePriceCommand::class, ['--date' => 'today']);

        $prices = MagentoPrice::all();

        $this->assertCount(1, $prices);
    }
}
