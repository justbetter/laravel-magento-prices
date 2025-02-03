<?php

namespace JustBetter\MagentoPrices\Data;

class PriceData extends Data
{
    public array $rules = [
        'sku' => ['required', 'max:255'],

        'base_prices' => ['nullable', 'array'],
        'base_prices.*.store_id' => ['required', 'integer'],
        'base_prices.*.price' => ['required', 'numeric'],

        'tier_prices' => ['nullable', 'array'],
        'tier_prices.*.website_id' => ['required', 'integer'],
        'tier_prices.*.customer_group' => ['required', 'string'],
        'tier_prices.*.price_type' => ['required', 'string'],
        'tier_prices.*.quantity' => ['required', 'numeric', 'min:1'],
        'tier_prices.*.price' => ['required', 'numeric'],

        'special_prices' => ['nullable', 'array'],
        'special_prices.*.store_id' => ['required', 'integer'],
        'special_prices.*.price' => ['required', 'numeric'],
        'special_prices.*.price_from' => ['required', 'date_format:Y-m-d H:i:s'],
        'special_prices.*.price_to' => ['required', 'date_format:Y-m-d H:i:s'],
    ];

    public function checksum(): string
    {
        $json = json_encode($this->validated());

        throw_if($json === false, 'Failed to generate checksum');

        return md5($json);
    }
}
