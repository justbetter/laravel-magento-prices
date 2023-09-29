<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use JustBetter\MagentoPrices\Contracts\ImportsGroups;
use JustBetter\MagentoPrices\Jobs\ImportGroupsJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class ImportGroupsJobTest extends TestCase
{
    /** @test */
    public function it_can_import_groups(): void
    {
        $this->mock(ImportsGroups::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('import')
                ->once();
        });

        ImportGroupsJob::dispatch();
    }
}
