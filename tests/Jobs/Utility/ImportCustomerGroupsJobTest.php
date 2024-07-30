<?php

namespace JustBetter\MagentoPrices\Tests\Jobs\Utility;

use JustBetter\MagentoPrices\Contracts\Utility\ImportsCustomerGroups;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ImportCustomerGroupsJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(ImportsCustomerGroups::class, function(MockInterface $mock): void {
            $mock->shouldReceive('import')->once();
        });

        ImportCustomerGroupsJob::dispatch();
    }
}
