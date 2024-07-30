<?php

namespace JustBetter\MagentoPrices\Tests\Commands\Update;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoPrices\Commands\Update\UpdateAllPricesCommand;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use PHPUnit\Framework\Attributes\Test;

class UpdateAllPricesCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_jobs(): void
    {
        Bus::fake([UpdatePriceJob::class]);

        MagentoProduct::query()->create(['sku' => '::sku_1::', 'exists_in_magento' => true]);
        MagentoProduct::query()->create(['sku' => '::sku_2::', 'exists_in_magento' => false]);
        MagentoProduct::query()->create(['sku' => '::sku_3::', 'exists_in_magento' => true]);

        Price::query()->create(['sku' => '::sku_1::']);
        Price::query()->create(['sku' => '::sku_2::']);
        Price::query()->create(['sku' => '::sku_3::']);
        Price::query()->create(['sku' => '::sku_4::']);

        $this->artisan(UpdateAllPricesCommand::class);

        Bus::assertDispatchedTimes(UpdatePriceJob::class, 2);
    }
}
