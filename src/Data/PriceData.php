<?php

namespace JustBetter\MagentoPrices\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Actions\CheckTierDuplicates;
use JustBetter\MagentoPrices\Contracts\DeterminesPricesEqual;
use JustBetter\MagentoPrices\Models\MagentoPrice;

class PriceData implements Arrayable
{
    public string $sku;

    /** @var Collection<int, BasePriceData> */
    public Collection $basePrices;

    /** @var Collection<int, TierPriceData> */
    public Collection $tierPrices;

    /** @var Collection<int, SpecialPriceData> */
    public Collection $specialPrices;

    public function __construct(
        string $sku,
        Collection $basePrices,
        ?Collection $tierPrices = null,
        ?Collection $specialPrices = null
    ) {
        $this->sku = $sku;
        $this->basePrices = $basePrices;
        $this->tierPrices = $tierPrices ?? collect();
        $this->specialPrices = $specialPrices ?? collect();
    }

    public function toArray(): array
    {
        return [
            'base_prices' => $this->basePrices->map(fn (BasePriceData $data) => $data->toArray()),
            'tier_prices' => $this->tierPrices->map(fn (TierPriceData $data) => $data->toArray()),
            'special_prices' => $this->specialPrices->map(fn (SpecialPriceData $data) => $data->toArray()),
        ];
    }

    public function getMagentoBasePrices(): array
    {
        $magentoPrices = [];

        /** @var BasePriceData $basePrice */
        foreach ($this->basePrices as $basePrice) {
            $magentoPrices[] = [
                'sku' => $this->sku,
                'price' => $basePrice->getPrice()->getAmount()->toFloat(),
                'store_id' => $basePrice->getStoreId(),
            ];
        }

        return $magentoPrices;
    }

    public function getMagentoTierPrices(): array
    {
        $magentoPrices = [];

        /** @var TierPriceData $tierPrice */
        foreach ($this->tierPrices as $tierPrice) {
            $magentoPrices[] = [
                'sku' => $this->sku,
                'price' => $tierPrice->getPrice()->getAmount()->toFloat(),
                'website_id' => $tierPrice->getStoreId(),
                'quantity' => $tierPrice->getQuantity() < 1 ? 1 : $tierPrice->getQuantity(),
                'customer_group' => $tierPrice->getGroupId(),
                'price_type' => $tierPrice->getPriceType(),
            ];
        }

        return $magentoPrices;
    }

    public function getMagentoSpecialPrices(): array
    {
        $magentoPrices = [];

        /** @var SpecialPriceData $specialPrice */
        foreach ($this->specialPrices as $specialPrice) {
            $magentoPrices[] = [
                'sku' => $this->sku,
                'price' => $specialPrice->getPrice()->getAmount()->toFloat(),
                'store_id' => $specialPrice->getStoreId(),
                'price_from' => $specialPrice->getFrom()->format('Y-m-d H:i:s'),
                'price_to' => $specialPrice->getTo()->format('Y-m-d H:i:s'),
            ];
        }

        return $magentoPrices;
    }

    public function getModel(): MagentoPrice
    {
        /** @var MagentoPrice $price */
        $price = MagentoPrice::query()
            ->firstOrCreate(['sku' => $this->sku]);

        return $price;
    }

    public function equals(self $other): bool
    {
        /** @var DeterminesPricesEqual $check */
        $check = app(DeterminesPricesEqual::class);

        return $check->equals($this, $other);
    }

    public function validate(): void
    {
        /** @var CheckTierDuplicates $tierDuplicates */
        $tierDuplicates = app(CheckTierDuplicates::class);

        $tierDuplicates->check($this->sku, $this->tierPrices);
    }
}
