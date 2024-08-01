<?php

namespace JustBetter\MagentoPrices\Tests\Actions\Update\Async;

use JustBetter\MagentoPrices\Actions\Update\Async\UpdatePricesAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesBasePricesAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesSpecialPricesAsync;
use JustBetter\MagentoPrices\Contracts\Update\Async\UpdatesTierPricesAsync;
use JustBetter\MagentoPrices\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdatePricesAsyncTest extends TestCase
{
    #[Test]
    public function it_calls_update_actions(): void
    {
        $this->mock(UpdatesBasePricesAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once()->andReturnTrue();
        });

        $this->mock(UpdatesTierPricesAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once()->andReturnTrue();
        });

        $this->mock(UpdatesSpecialPricesAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once()->andReturnTrue();
        });
        /** @var UpdatePricesAsync $action */
        $action = app(UpdatePricesAsync::class);
        $action->update(collect());
    }
}
