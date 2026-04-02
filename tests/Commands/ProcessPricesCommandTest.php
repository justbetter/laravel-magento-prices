<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\ProcessPricesCommand;
use JustBetter\MagentoPrices\Jobs\ProcessPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ProcessPricesCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake([ProcessPricesJob::class]);

        $this->artisan(ProcessPricesCommand::class);

        Bus::assertDispatched(ProcessPricesJob::class);
    }
}
