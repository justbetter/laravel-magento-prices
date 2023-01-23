<?php

namespace JustBetter\MagentoPrices\Tests\Retriever;

use Illuminate\Support\Enumerable;
use JustBetter\MagentoPrices\Retriever\SkuRetriever;
use JustBetter\MagentoPrices\Tests\TestCase;

class DummySkuRetriever extends SkuRetriever
{
    public function retrieveAll(): Enumerable
    {
        return collect();
    }
}

class SkuRetrieverTest extends TestCase
{
    public function test_it_retrieves_by_date(): void
    {
        $dummy = new DummySkuRetriever();
        $this->assertCount(0, $dummy->retrieveByDate(now()));
    }
}
