<?php

namespace JustBetter\MagentoPrices\Tests\Commands\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Retrieval\RetrievePriceCommand;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrievePriceCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake([RetrievePriceJob::class]);

        $this->artisan(RetrievePriceCommand::class, ['sku' => '::sku::']);

        Bus::assertDispatched(RetrievePriceJob::class);
    }
}
