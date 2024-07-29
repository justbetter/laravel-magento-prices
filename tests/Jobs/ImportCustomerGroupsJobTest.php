<?php

namespace JustBetter\MagentoPrices\Tests\Jobs;

use JustBetter\MagentoPrices\Contracts\Utility\ImportsCustomerGroups;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;

class ImportCustomerGroupsJobTest extends TestCase
{
    /** @test */
    public function it_can_import_customer_groups(): void
    {
        $this->mock(ImportsCustomerGroups::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('import')
                ->once();
        });

        ImportCustomerGroupsJob::dispatch();
    }
}
