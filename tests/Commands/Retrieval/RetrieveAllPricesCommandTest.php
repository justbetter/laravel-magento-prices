<?php

namespace JustBetter\MagentoPrices\Tests\Commands\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Retrieval\RetrieveAllPricesCommand;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrieveAllPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveAllPricesCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake([RetrieveAllPricesJob::class]);

        $this->artisan(RetrieveAllPricesCommand::class);

        Bus::assertDispatched(RetrieveAllPricesJob::class);
    }
}
