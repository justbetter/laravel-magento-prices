<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\Utility\FiltersTierPrices;
use JustBetter\MagentoPrices\Contracts\Utility\RetrievesCustomerGroups;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class FilterTierPrices implements FiltersTierPrices
{
    public function __construct(
        protected RetrievesCustomerGroups $customerGroups
    ) {}

    public function filter(string $sku, array $tierPrices): array
    {
        /** @var ?MagentoProduct $magentoProduct */
        $magentoProduct = MagentoProduct::query()
            ->where('sku', '=', $sku)
            ->whereNotNull('data')
            ->first();

        $websiteIds = data_get($magentoProduct->data ?? [], 'extension_attributes.website_ids', []);
        $websiteIds[] = 0; // Always include global prices

        // Only filter website ids if the product is found and has data
        $shouldFilterWebsiteIds = $magentoProduct?->data !== null;

        return collect($tierPrices)
            ->whereIn('customer_group', $this->customerGroups->retrieve())
            ->when($shouldFilterWebsiteIds, fn (Collection $tierPrices): Collection => $tierPrices->whereIn('website_id', $websiteIds))
            ->values()
            ->toArray();
    }

    public static function bind(): void
    {
        app()->singleton(FiltersTierPrices::class, static::class);
    }
}
