<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Utility\ImportsCustomerGroups;
use JustBetter\MagentoPrices\Models\CustomerGroup;
use JustBetter\MagentoPrices\Models\Price;

class ImportCustomerGroups implements ImportsCustomerGroups
{
    public function __construct(
        protected Magento $magento
    ) {
    }

    public function import(): void
    {
        $date = now();

        $this->magento->lazy('customerGroups/search')
            ->each(function (array $group) use ($date): void {
                CustomerGroup::query()->updateOrCreate([
                    'code' => $group['code'],
                ], [
                    'data' => $group,
                    'imported_at' => $date,
                ]);
            });

        $deletions = CustomerGroup::query()
            ->where('imported_at', '<', $date)
            ->orWhereNull('imported_at')
            ->get();

        $newGroupCount = CustomerGroup::query()
            ->where('created_at', '>=', $date)
            ->count();

        // If a new group has been added, tier prices may be missing.
        if ($newGroupCount > 0) {
            Price::query()
                ->where('sync', '=', true)
                ->update(['update' => true]);
        }

        // Previous updates may have failed if a group has been removed.
        if ($deletions->isNotEmpty()) {
            Price::query()->update([
                'sync' => true,
                'update' => true,
            ]);
        }

        $deletions->each(function (CustomerGroup $group): void {
            $group->delete();
        });
    }

    public static function bind(): void
    {
        app()->singleton(ImportsCustomerGroups::class, static::class);
    }
}
