<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\ImportsCustomerGroups;
use JustBetter\MagentoPrices\Models\MagentoCustomerGroup;
use JustBetter\MagentoPrices\Models\MagentoPrice;

class ImportCustomerGroups implements ImportsCustomerGroups
{
    public function __construct(
        protected Magento $magento
    ) {
    }

    public function import(): void
    {
        $date = now();

        $this->magento->lazy('customerGroups/search')->each(function (array $group) use ($date): void {
            MagentoCustomerGroup::query()->updateOrCreate([
                'code' => $group['code'],
            ], [
                'data' => $group,
                'imported_at' => $date,
            ]);
        });

        $deletions = MagentoCustomerGroup::query()
            ->where('imported_at', '<', $date)
            ->orWhereNull('imported_at')
            ->get();

        $newGroupCount = MagentoCustomerGroup::query()
            ->where('created_at', '>=', $date)
            ->count();

        // If a new group has been added, tier prices may be missing.
        if ($newGroupCount > 0) {
            MagentoPrice::query()
                ->where('sync', '=', true)
                ->update(['update' => true]);
        }

        // Previous updates may have failed if a group has been removed.
        if ($deletions->isNotEmpty()) {
            MagentoPrice::query()->update([
                'sync' => true,
                'update' => true,
            ]);
        }

        $deletions->each(function (MagentoCustomerGroup $group): void {
            $group->delete();
        });
    }

    public static function bind(): void
    {
        app()->singleton(ImportsCustomerGroups::class, static::class);
    }
}
