<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

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
            ->each(function (array $specialPrices): void {
                $this->magento
                    ->post('products/special-price-delete', ['prices' => $specialPrices])
                    ->throw();
            });
    }

    public static function bind(): void
    {
        app()->singleton(DeletesCurrentSpecialPrices::class, static::class);
    }
}
