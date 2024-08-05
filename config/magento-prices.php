<?php

return [
    'repository' => \JustBetter\MagentoPrices\Repository\Repository::class,

    /* Queue for the jobs to run on */
    'queue' => 'default',

    /* Send updates using Magento 2's async bulk endpoints, a configured message queue in Magento is required for this */
    'async' => false,
];
