<?php

namespace JustBetter\MagentoPrices\Actions;

use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoClient\Requests\Products;
use JustBetter\MagentoPrices\Contracts\FindsProductsWithMissingPrices;

class FindProductsWithMissingPrices implements FindsProductsWithMissingPrices
{
    public function __construct(protected Products $products)
    {
    }

    public function retrieve(): Enumerable
    {
        return LazyCollection::make(function () {
            $searchCriteria = SearchCriteria::make()
                ->select(['sku', 'price', 'type_id']);

            $lazyProducts = $this->products->lazy($searchCriteria);

            foreach ($lazyProducts as $product) {
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
