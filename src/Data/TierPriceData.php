<?php

namespace JustBetter\MagentoPrices\Data;

use Brick\Money\Money;
use Illuminate\Contracts\Support\Arrayable;

class TierPriceData implements Arrayable
{
    public int $storeId;

    public int $quantity;

    public string $groupId;

    public string $priceType;

    public Money $price;

    public function __construct(
        string $groupId,
        Money $price,
        int $quantity = 1,
        int $storeId = 0,
        string $priceType = 'fixed'
    ) {
        $this->groupId = $groupId;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->storeId = $storeId;
        $this->priceType = $priceType;
    }

    public function getPriceType(): string
    {
        return $this->priceType;
    }

    public function setPriceType(string $priceType): void
    {
        $this->priceType = $priceType;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function setGroupId(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    /** Get unique identifier for this store, qty and group */
    public function getIdentifier(): string
    {
        return implode('-', [$this->storeId, $this->quantity, $this->groupId]);
    }

    public function equals(self $other): bool
    {
        return $this->price->isEqualTo($other->price) &&
            $this->groupId === $other->groupId &&
            $this->storeId === $other->storeId &&
            $this->priceType === $other->priceType;
    }

    public function toArray(): array
    {
        return [
            'storeId' => $this->storeId,
            'quantity' => $this->quantity,
            'customer_group' => $this->groupId,
            'price' => (string) $this->price->getAmount(),
        ];
    }
}
