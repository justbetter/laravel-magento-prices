<?php

namespace JustBetter\MagentoPrices\Actions\Update\Sync;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoPrices\Contracts\Update\Sync\UpdatesTierPrice;
use JustBetter\MagentoPrices\Contracts\Utility\RetrievesCustomerGroups;
use JustBetter\MagentoPrices\Models\Price;

class UpdateTierPrice implements UpdatesTierPrice
{
    public function __construct(
        protected Magento $magento,
        protected RetrievesCustomerGroups $customerGroups
    ) {}

    public function update(Price $price): bool
    {
        $payload = collect($price->tier_prices)
            ->whereIn('customer_group', $this->customerGroups->retrieve())
            ->map(fn (array $tierPrice): array => array_merge($tierPrice, [
                'sku' => $price->sku,
            ]));

        if ($payload->isEmpty()) {
            return true;
        }

        $response = $this->magento
            ->post('products/tier-prices', ['prices' => $payload])
            ->onError(function (Response $response) use ($price, $payload): void {
                activity()
                    ->on($price)
                    ->useLog('error')
                    ->withProperties([
                        'response' => $response->body(),
                        'payload' => $payload,
                    ])
                    ->log('Failed to update tier price');
            });

        return $response->successful();
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesTierPrice::class, static::class);
    }
}
