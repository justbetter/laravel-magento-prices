<?php

namespace JustBetter\MagentoPrices\Data;

use Brick\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use JustBetter\MagentoPrices\Helpers\MoneyHelper;

class BasePriceData implements Arrayable
{
    public Money $price;

    public int $storeId = 0;

    public function __construct(Money $price, int $storeId = 0)
    {
        $this->price = $price;
        $this->storeId = $storeId;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function parsePrice(mixed $price): static
    {
        /** @var MoneyHelper $helper */
        $helper = app(MoneyHelper::class);
        $this->price = $helper->getMoney($price);

        return $this;
    }

    public function equals(self $other): bool
    {
        return $this->price->isEqualTo($other->price) &&
            $this->storeId === $other->storeId;
    }

    public function toArray(): array
    {
        return [
            'storeId' => $this->storeId,
            'price' => (string) $this->price->getAmount(),
        ];
    }
}
