<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use Illuminate\Support\Collection;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Utility\DeletesCurrentSpecialPrices;

class DeleteCurrentSpecialPrices implements DeletesCurrentSpecialPrices
{
    public function __construct(protected Magento $magento) {}

    public function delete(array $skus): void
    {
        $this->magento
            ->post('products/special-price-information', ['skus' => $skus])
            ->throw()
            ->collect()
            ->chunk(100)
            ->each(function (Collection $specialPrices): void {
                $this->magento
                    ->post('products/special-price-delete', ['prices' => $specialPrices->toArray()])
                    ->throw();
            });
    }

    public static function bind(): void
    {
        app()->singleton(DeletesCurrentSpecialPrices::class, static::class);
    }
}
