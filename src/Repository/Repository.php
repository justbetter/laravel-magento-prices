<?php

namespace JustBetter\MagentoPrices\Repository;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Exceptions\NotImplementedException;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use Illuminate\Support\Carbon;

class Repository extends BaseRepository
{
    public function retrieve(string $sku): ?PriceData
    {
        throw new NotImplementedException;
    }

    public function skus(?Carbon $from = null): Collection
    {
        /** @var Collection<int, string> $skus */
        $skus = MagentoProduct::query()
            ->where('exists_in_magento', '=', true)
            ->pluck('sku');

        return $skus;
    }
}
