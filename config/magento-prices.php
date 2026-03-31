<?php

use JustBetter\MagentoPrices\Repository\Repository;

return [
    'repository' => Repository::class,

    /* Queue for the jobs to run on */
    'queue' => 'default',

    /* Send updates using Magento 2's async bulk endpoints, a configured message queue in Magento is required for this */
    'async' => false,

    /* Number of hours before async bulk operations are considered stale and prices can be re-queued */
    'async_stale_hours' => 24,
];
