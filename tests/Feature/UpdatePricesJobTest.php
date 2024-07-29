<?php

namespace JustBetter\MagentoPrices\Tests\Feature;

use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Retrieval\RetrievePriceCommand;
use JustBetter\MagentoPrices\Commands\Update\UpdatePriceCommand;
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

        $this->artisan(RetrievePriceCommand::class);
    }

    public function test_it_updates(): void
    {
        Bus::fake([UpdateMagentoBasePricesJob::class]);

        $this->artisan(UpdatePriceCommand::class, ['sku' => '123']);

        Bus::assertBatched(function (PendingBatch $batch) {
            foreach ($batch->jobs as $job) {
                if ($job instanceof UpdateMagentoBasePricesJob) {
                    return true;
                }
            }

            return false;
        });
    }
}
