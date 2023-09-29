<?php

namespace JustBetter\MagentoPrices\Actions;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\ImportsGroups;
use JustBetter\MagentoPrices\Models\MagentoGroup;
use JustBetter\MagentoPrices\Models\MagentoPrice;

class ImportGroups implements ImportsGroups
{
    public function __construct(
        protected Magento $magento
    ) {
    }

    public function import(): void
    {
        $date = now();

        $this->magento->lazy('customerGroups/search')->each(function (array $group) use ($date): void {
            MagentoGroup::query()->updateOrCreate([
                'code' => $group['code'],
            ], [
                'data' => $group,
                'imported_at' => $date,
            ]);
        });

        $deletions = MagentoGroup::query()
            ->where('imported_at', '<', $date)
            ->orWhereNull('imported_at')
            ->get();

        $newGroupCount = MagentoGroup::query()
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

        $deletions->each(function (MagentoGroup $magentoGroup): void {
            $magentoGroup->delete();
        });
    }

    public static function bind(): void
    {
        app()->singleton(ImportsGroups::class, static::class);
    }
}
