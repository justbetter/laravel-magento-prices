<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoPrices\Contracts\FindsProductsWithMissingPrices;

class FindProductsWithMissingPrices implements FindsProductsWithMissingPrices
{
    public function __construct(protected Magento $magento)
    {
    }

    public function retrieve(): Enumerable
    {
        return LazyCollection::make(function () {
            $searchCriteria = SearchCriteria::make()
                ->select(['sku', 'price', 'type_id'])
                ->get();

            $products = $this->magento->lazy('products', $searchCriteria);

            foreach ($products as $product) {
                if (
                    (array_key_exists('price', $product) && floatval($product['price']) > 0) ||
                    (array_key_exists('type_id', $product) && $product['type_id'] != 'simple')
                ) {
                    continue;
                }

                yield $product['sku'];
            }
        });
    }

    public static function bind(): void
    {
        app()->singleton(FindsProductsWithMissingPrices::class, static::class);
    }
}
