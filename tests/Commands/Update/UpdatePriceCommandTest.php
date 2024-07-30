<?php

namespace JustBetter\MagentoPrices\Tests\Commands\Update;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Update\UpdatePriceCommand;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdatePriceCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake([UpdatePriceJob::class]);

        Price::query()->create(['sku' => '::sku_1::']);

        $this->artisan(UpdatePriceCommand::class, [
            'sku' => '::sku_1::',
        ]);

        Bus::assertDispatched(UpdatePriceJob::class);
    }

    #[Test]
    public function it_throws_exception_on_missing_price(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->artisan(UpdatePriceCommand::class, [
            'sku' => '::some-non-existent-sku::',
        ]);
    }
}
