<?php

namespace JustBetter\MagentoPrices\Tests\Fakes;

use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Repository\Repository;

class FakeNullRepository extends Repository
{
    public function retrieve(string $sku): ?PriceData
    {
        return null;
    }
}
