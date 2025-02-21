<?php

namespace JustBetter\MagentoPrices\Listeners;

use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoProducts\Events\ProductDataModifiedEvent;

class ProductDataModifiedListener
{
    public function handle(ProductDataModifiedEvent $event): void
    {
        if ($event->oldData === null) {
            return;
        }

        /** @var array<int, int> $oldWebsiteIds */
        $oldWebsiteIds = data_get($event->oldData, 'extension_attributes.website_ids', []);

        /** @var array<int, int> $newWebsiteIds */
        $newWebsiteIds = data_get($event->newData, 'extension_attributes.website_ids', []);

        $oldWebsiteIds = collect($oldWebsiteIds);
        $newWebsiteIds = collect($newWebsiteIds);

        $modified = $oldWebsiteIds->count() !== $newWebsiteIds->count() ||
            $oldWebsiteIds->diff($newWebsiteIds)->isNotEmpty() ||
            $newWebsiteIds->diff($oldWebsiteIds)->isNotEmpty();

        if ($modified) {
            Price::query()
                ->where('sku', '=', $event->sku)
                ->update(['update' => true]);
        }
    }
}
