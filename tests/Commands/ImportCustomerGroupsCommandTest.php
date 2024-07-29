<?php

namespace JustBetter\MagentoPrices\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\PendingCommand;
use JustBetter\MagentoPrices\Commands\Utility\ImportCustomerGroupsCommand;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Tests\TestCase;

class ImportCustomerGroupsCommandTest extends TestCase
{
    /** @test */
    public function it_can_import_customer_groups(): void
    {
        Bus::fake();

        /** @var PendingCommand $command */
        $command = $this->artisan(ImportCustomerGroupsCommand::class);

        $command
            ->assertSuccessful()
            ->run();

        Bus::assertDispatched(ImportCustomerGroupsJob::class);
    }
}
