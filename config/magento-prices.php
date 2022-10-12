<?php

use Brick\Math\RoundingMode;
use JustBetter\MagentoPrices\Retriever\DummyPriceRetriever;
use JustBetter\MagentoPrices\Retriever\DummySkuRetriever;

return [
    /* Register your price and sku retrievers here */
    'retrievers' => [
        'sku' => DummySkuRetriever::class,
        'price' => DummyPriceRetriever::class,
    ],

    'currency' => 'EUR',
    'precision' => 4,
    'rounding_mode' => RoundingMode::HALF_UP,

    /* How many times can a price update failed before being cancelled */
    'fail_count' => 5,

    /* How many price retrieval jobs may be dispatched per sync */
    'retrieve_limit' => 25,

    /* How many prices update jobs may be dispatched per sync */
    'update_limit' => 100,

    /* Queue for the jobs to run on */
    'queue' => 'default',

    'monitor' => [
        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'retrieval_max_wait' => 30,

        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'update_max_wait' => 30,
    ]
];
