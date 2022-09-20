<?php

namespace JustBetter\MagentoPrices\Tests\Feature;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\RetrievePricesCommand;
use JustBetter\MagentoPrices\Commands\UpdatePriceCommand;
use JustBetter\MagentoPrices\Jobs\UpdateMagentoBasePricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use Mockery\MockInterface;

class UpdatePricesJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')
                ->andReturnTrue();
        });

        $this->artisan(RetrievePricesCommand::class);
    }

    public function test_it_updates(): void
    {
        Bus::fake([UpdateMagentoBasePricesJob::class]);

        $this->artisan(UpdatePriceCommand::class, ['sku' => '123']);

        Bus::assertDispatched(UpdateMagentoBasePricesJob::class);
    }
}
