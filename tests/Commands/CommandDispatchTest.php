<?php

namespace JustBetter\MagentoPrices\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\RetrievePricesCommand;
use JustBetter\MagentoPrices\Commands\SearchMissingPricesCommand;
use JustBetter\MagentoPrices\Commands\SyncPricesCommand;
use JustBetter\MagentoPrices\Commands\UpdatePriceCommand;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\RetrievePricesJob;
use JustBetter\MagentoPrices\Jobs\SyncMissingPricesJob;
use JustBetter\MagentoPrices\Jobs\SyncPricesJob as SyncPricesJob;
use JustBetter\MagentoPrices\Jobs\UpdatePriceJob;
use JustBetter\MagentoPrices\Tests\TestCase;

class CommandDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    /** @dataProvider dataProvider */
    public function test_simple_commands(string $command, string $job): void
    {
        $this->artisan($command);

        Bus::assertDispatched($job);
    }

    public function test_retrieve_price(): void
    {
        $this->artisan(RetrievePricesCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(RetrievePriceJob::class);
    }

    public function test_retrieve_prices(): void
    {
        $this->artisan(RetrievePricesCommand::class);

        Bus::assertDispatched(RetrievePricesJob::class);
    }

    public function test_update_price(): void
    {
        $this->artisan(UpdatePriceCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(UpdatePriceJob::class);
    }

    public function dataProvider(): array
    {
        return [
            'Sync prices' => [
                SyncPricesCommand::class,
                SyncPricesJob::class,
            ],
            'Search missing' => [
                SearchMissingPricesCommand::class,
                SyncMissingPricesJob::class,
            ],
        ];
    }
}
