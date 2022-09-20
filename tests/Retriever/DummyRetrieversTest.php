<?php

namespace JustBetter\MagentoPrices\Tests\Retriever;

use JustBetter\MagentoPrices\Retriever\DummyPriceRetriever;
use JustBetter\MagentoPrices\Tests\TestCase;

class DummyRetrieversTest extends TestCase
{
    public function test_dummy_sku_retriever(): void
    {
        $retriever = new \JustBetter\MagentoPrices\Retriever\DummySkuRetriever();

        $this->assertEquals(['123', '456'], $retriever->retrieveAll()->toArray());
        $this->assertEquals(['789'], $retriever->retrieveByDate(now())->toArray());
    }

    public function test_dummy_price_retriever(): void
    {
        $retriever = new DummyPriceRetriever();

        $price = $retriever->retrieve('::sku::');

        $this->assertEquals('::sku::', $price->sku);
        $this->assertCount(1, $price->basePrices);
        $this->assertCount(0, $price->tierPrices);
        $this->assertCount(0, $price->specialPrices);
    }
}
