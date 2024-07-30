<?php

namespace JustBetter\MagentoPrices\Tests\Commands\Utility;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Utility\ProcessProductsWithMissingPricesCommand;
use JustBetter\MagentoPrices\Jobs\Utility\ProcessProductsWithMissingPricesJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProcessProductsWithMissingPricesCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake([ProcessProductsWithMissingPricesJob::class]);

        $this->artisan(ProcessProductsWithMissingPricesCommand::class);

        Bus::assertDispatched(ProcessProductsWithMissingPricesJob::class);
    }
}
