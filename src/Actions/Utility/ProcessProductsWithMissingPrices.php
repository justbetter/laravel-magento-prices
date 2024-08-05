<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoPrices\Contracts\Utility\ProcessesProductsWithMissingPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePriceJob;
use JustBetter\MagentoPrices\Jobs\Update\UpdatePricesAsyncJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class ProcessProductsWithMissingPrices implements ProcessesProductsWithMissingPrices
{
    public function __construct(protected Magento $magento) {}

    public function process(): void
    {
        $skus = $this->retrieveSkus();

        /** @var bool $async */
        $async = config('magento-prices.async');

        $pricesToUpdate = collect();

        foreach ($skus as $sku) {
            /** @var ?Price $price */
            $price = Price::query()->firstWhere('sku', '=', $sku);

            if ($price !== null) {

                if ($async) {
                    $pricesToUpdate[] = $price;
                } else {
                    UpdatePriceJob::dispatch($price);
                }

                continue;
            }

            RetrievePriceJob::dispatch($sku);
        }

        if ($async) {
            $repository = BaseRepository::resolve();

            $pricesToUpdate
                ->chunk($repository->updateLimit())
                ->each(fn (Collection $chunk): PendingDispatch => UpdatePricesAsyncJob::dispatch($chunk));
        }
    }

    /** @return LazyCollection<int, string> */
    public function retrieveSkus(): LazyCollection
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
        app()->singleton(ProcessesProductsWithMissingPrices::class, static::class);
    }
}
