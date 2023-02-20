<?php

namespace JustBetter\MagentoPrices\Data;

use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use JustBetter\MagentoPrices\Helpers\MoneyHelper;

class SpecialPriceData implements Arrayable
{
    public Money $price;

    public int $storeId = 0;

    public Carbon $from;

    public Carbon $to;

    public function __construct(Money $price, int $storeId = 0, Carbon $from = null, Carbon $to = null)
    {
        $this->price = $price;
        $this->storeId = $storeId;
        $this->from = $from ?? Carbon::createFromTimestamp(0);
        $this->to = $to ?? Carbon::createFromTimestamp(2147483647);
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

    public function getFrom(): Carbon
    {
        return $this->from;
    }

    public function setFrom(Carbon $from): void
    {
        $this->from = $from;
    }

    public function getTo(): Carbon
    {
        return $this->to;
    }

    public function setTo(Carbon $to): void
    {
        $this->to = $to;
    }

    public function parsePrice(mixed $price): self
    {
        /** @var MoneyHelper $helper */
        $helper = app(MoneyHelper::class);
        $this->price = $helper->getMoney($price);

        return $this;
    }

    public function equals(self $other): bool
    {
        if (! $this->price->isEqualTo($other->price)) {
            return false;
        }

        if (! $this->from->startOfDay()->equalTo($other->from->startOfDay()) || ! $this->to->startOfDay()->equalTo($other->to->startOfDay())) {
            return false;
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'storeId' => $this->storeId,
            'price' => (string) $this->price->getAmount(),
            'price_from' => $this->from->format('Y-m-d H:i:s'),
            'price_to' => $this->to->format('Y-m-d H:i:s'),
        ];
    }
}
