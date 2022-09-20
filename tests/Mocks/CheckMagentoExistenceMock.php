<?php

namespace JustBetter\MagentoPrices\Tests\Mocks;

use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;

class CheckMagentoExistenceMock implements ChecksMagentoExistence
{
    public function exists(string $sku): bool
    {
        return true;
    }
}
