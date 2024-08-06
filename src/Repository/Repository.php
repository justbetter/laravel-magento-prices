<?php

namespace JustBetter\MagentoPrices\Repository;

use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Exceptions\NotImplementedException;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class Repository extends BaseRepository
{
    public function retrieve(string $sku): ?PriceData
    {
        throw new NotImplementedException;
    }

    public function skus(?Carbon $from = null): Enumerable
    {
        /** @var Enumerable<int, string> $skus */
        $skus = MagentoProduct::query()
            ->where('exists_in_magento', '=', true)
            ->select(['sku'])
            ->distinct()
            ->pluck('sku');

        return $skus;
    }
}
