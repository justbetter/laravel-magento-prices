<?php

namespace JustBetter\MagentoPrices\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\MonitorWaitTimesCommand;
use JustBetter\MagentoPrices\Commands\Retrieval\RetrievePriceCommand;
use JustBetter\MagentoPrices\Commands\ProcessPricesCommand;
use JustBetter\MagentoPrices\Commands\Update\UpdatePriceCommand;
use JustBetter\MagentoPrices\Commands\Utility\ProcessProductsWithMissingPricesCommand;
use JustBetter\MagentoPrices\Jobs\MonitorWaitTimesJob;
use JustBetter\MagentoPrices\Jobs\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\RetrievePricesJob;
use JustBetter\MagentoPrices\Jobs\SyncPricesJob as SyncPricesJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Utility\ProcessProductsWithMissingPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;

class CommandDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    /** @dataProvider dataProvider */
    public function test_simple_commands(string $command, string $job, array $args = []): void
    {
        $this->artisan($command, $args);

        Bus::assertDispatched($job);
    }

    public function test_retrieve_price(): void
    {
        $this->artisan(RetrievePriceCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(RetrievePriceJob::class);
    }

    public function test_retrieve_prices(): void
    {
        $this->artisan(RetrievePriceCommand::class);

        Bus::assertDispatched(RetrievePricesJob::class);
    }

    public function test_update_price(): void
    {
        $this->artisan(UpdatePriceCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(UpdatePriceJob::class);
    }

    public static function dataProvider(): array
    {
        return [
            'Sync prices' => [
                ProcessPricesCommand::class,
                SyncPricesJob::class,
            ],
            'Sync prices sync' => [
                ProcessPricesCommand::class,
                SyncPricesJob::class,
                ['--sync' => true],
            ],
            'Search missing' => [
                ProcessProductsWithMissingPricesCommand::class,
                ProcessProductsWithMissingPricesJob::class,
            ],
            'Monitor wait times' => [
                MonitorWaitTimesCommand::class,
                MonitorWaitTimesJob::class,
            ],
        ];
    }
}
