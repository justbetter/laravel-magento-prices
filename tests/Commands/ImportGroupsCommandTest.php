<?php

namespace JustBetter\MagentoPrices\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\PendingCommand;
use JustBetter\MagentoPrices\Commands\ImportGroupsCommand;
use JustBetter\MagentoPrices\Jobs\ImportGroupsJob;
use JustBetter\MagentoPrices\Tests\TestCase;

class ImportGroupsCommandTest extends TestCase
{
    /** @test */
    public function it_can_import_groups(): void
    {
        Bus::fake();

        /** @var PendingCommand $command */
        $command = $this->artisan(ImportGroupsCommand::class);

        $command
            ->assertSuccessful()
            ->run();

        Bus::assertDispatched(ImportGroupsJob::class);
    }
}
