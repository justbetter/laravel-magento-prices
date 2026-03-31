<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Commands\Utility;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Utility\ImportCustomerGroupsCommand;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ImportCustomerGroupsCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake([ImportCustomerGroupsJob::class]);

        $this->artisan(ImportCustomerGroupsCommand::class);

        Bus::assertDispatched(ImportCustomerGroupsJob::class);
    }
}
