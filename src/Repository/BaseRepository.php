<?php

namespace JustBetter\MagentoPrices\Repository;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Data\PriceData;

abstract class BaseRepository
{
    protected int $retrieveLimit = 250;

    protected int $updateLimit = 250;

    protected int $failLimit = 3;

    public function retrieveLimit(): int
    {
        return $this->retrieveLimit;
    }

    public function updateLimit(): int
    {
        return $this->updateLimit;
    }

    public function failLimit(): int
    {
        return $this->failLimit;
    }

    public static function resolve(): BaseRepository
    {
        /** @var ?class-string<BaseRepository> $repository */
        $repository = config('magento-prices.repository');

        throw_if($repository === null, 'Repository has not been found.');

        /** @var BaseRepository $instance */
        $instance = app($repository);

        return $instance;
    }

    /** @return Collection<int, string> */
    abstract public function skus(?Carbon $from = null): Collection;

    abstract public function retrieve(string $sku): ?PriceData;
}
