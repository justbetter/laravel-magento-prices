<?php

namespace JustBetter\MagentoPrices\Actions\Utility;

use JustBetter\MagentoPrices\Exceptions\PriceUpdateException;
use JustBetter\MagentoPrices\Jobs\Utility\ImportCustomerGroupsJob;
use JustBetter\MagentoPrices\Models\CustomerGroup;

class RetrieveCustomerGroups
{
    public function retrieve(): array
    {
        cache()->remember('magento:prices:customer:groups:imported', now()->addDay(), function (): bool {
            ImportCustomerGroupsJob::dispatch();

            return true;
        });

        $groups = CustomerGroup::query()->pluck('code');

        if ($groups->isEmpty()) {
            throw new PriceUpdateException('The Magento customer groups are not imported');
        }

        return $groups
            ->push('ALL GROUPS')
            ->toArray();
    }
}
