<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Utility;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Actions\Utility\RetrieveCustomerGroups;
use JustBetter\MagentoPrices\Exceptions\PriceUpdateException;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Models\CustomerGroup;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveCustomerGroupsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([ImportCustomerGroupsJob::class]);
    }

    #[Test]
    public function it_dispatches_job_and_throws_exception(): void
    {
        $this->expectException(PriceUpdateException::class);

        /** @var RetrieveCustomerGroups $action */
        $action = app(RetrieveCustomerGroups::class);
        $action->retrieve();

        Bus::assertDispatched(ImportCustomerGroupsJob::class);
    }

    #[Test]
    public function it_returns_groups(): void
    {
        CustomerGroup::query()->create(['code' => '::group::', 'data' => []]);

        /** @var RetrieveCustomerGroups $action */
        $action = app(RetrieveCustomerGroups::class);
        $this->assertEquals(['::group::', 'ALL GROUPS'], $action->retrieve());

        Bus::assertDispatched(ImportCustomerGroupsJob::class);
    }

    #[Test]
    public function it_does_not_dispatch_import_job_twice(): void
    {
        CustomerGroup::query()->create(['code' => '::group::', 'data' => []]);

        /** @var RetrieveCustomerGroups $action */
        $action = app(RetrieveCustomerGroups::class);
        $action->retrieve();
        $action->retrieve();

        Bus::assertDispatchedTimes(ImportCustomerGroupsJob::class, 1);
    }
}
