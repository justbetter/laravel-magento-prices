<?php

declare(strict_types=1);

namespace JustBetter\MagentoPrices\Actions\Utility;

use Illuminate\Support\Collection;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Utility\DeletesCurrentTierPrices;

class DeleteCurrentTierPrices implements DeletesCurrentTierPrices
{
    public function __construct(protected Magento $magento) {}

    public function delete(array $skus): void
    {
        $this->magento
            ->post('products/tier-prices-information', ['skus' => $skus])
            ->throw()
            ->collect()
            ->chunk(100)
            ->each(function (Collection $tierPrices): void {
                $this->magento
                    ->post('products/tier-prices-delete', ['prices' => $tierPrices->toArray()])
                    ->throw();
            });
    }

    public static function bind(): void
    {
        app()->singleton(DeletesCurrentTierPrices::class, static::class);
    }
}
