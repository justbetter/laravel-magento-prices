<?php

namespace JustBetter\MagentoPrices\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\ProcessPricesCommand;
use JustBetter\MagentoPrices\Jobs\ProcessPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProcessPricesCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
       Bus::fake([ProcessPricesJob::class]);

       $this->artisan(ProcessPricesCommand::class);

       Bus::assertDispatched(ProcessPricesJob::class);
    }
}
