# Laravel Magento Prices

Package to send prices to Magento from a Laravel application using a configurable source.

## Features
The idea is that we want to push prices to Magento but we do not want to rewrite the logic of keeping track and updating prices to Magento.
This package can:

- Retrieve prices from any source
- Push prices to Magento (base / tier / special)
- Only update prices in Magento when are modified. i.e. when you retrieve the same price ten times it only updates once to Magento
- Search for missing prices in Magento
- Automatically stop syncing when updating fails
- Logs activities using [Spatie activitylog](https://github.com/spatie/laravel-activitylog)
- Logs errors using [JustBetter Error Logger](https://github.com/justbetter/laravel-error-logger)
- Checks if Magento products exist using [JustBetter Magento Products](https://github.com/justbetter/laravel-magento-products)

> Also using customer specific prices? [See our other package!](https://github.com/justbetter/laravel-magento-customer-prices)
> We also have a [Magento Client](https://github.com/justbetter/laravel-magento-client) to easily connect Laravel to Magento!

## Installation

Require this package: `composer require justbetter/laravel-magento-prices`

Publish the config
```
php artisan vendor:publish --provider="JustBetter\MagentoPrices\ServiceProvider" --tag="config"
```

Publish the activity log's migrations:
```
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
```

Run migrations.

## Usage

Add the following commands to your scheduler:
```php
<?php

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(\JustBetter\MagentoPrices\Commands\SyncPricesCommand::class)->everyMinute();

        $schedule->command(\JustBetter\MagentoPrices\Commands\RetrievePricesCommand::class)->daily();
        // Or for example
        $schedule->command(\JustBetter\MagentoPrices\Commands\RetrievePricesCommand::class)->weekly(); // Retrieve all weekly
        $schedule->command('price:retrieve --date=today')->dailyAt('23:00'); // Retrieve updated daily
    }

```


### Retrieving Prices

In order to retrieve prices you have to create two classes. One to retrieve skus and one to retrieve the price(s) per sku.

#### Price retriever

This class is responsible for retrieving prices for products. Your class must extend `\JustBetter\MagentoPrices\Retriever\PriceRetriever`.
Your class must return a `PriceData` object or `null`.
The `PriceData` object is a wrapper around three collections:
- Base prices
- Tier prices
- Special Prices

See the classes `BasePriceData` and `TierPriceData` in the `JustBetter\MagentoPrices\Data` namespace on how to use them.

For example:

```php
<?php

namespace JustBetter\MagentoPrices\Retriever;

use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Helpers\MoneyHelper;

class ExamplePriceRetriever extends PriceRetriever
{
    public function __construct(protected ExternalService $externalService, protected MoneyHelper $moneyHelper) {}

    public function retrieve(string $sku): ?PriceData
    {
        $basePrice = new BasePriceData(
            $this->moneyHelper->getMoney($this->externalService->getBasePrice($sku))
        );

        $tierPrices = $this->externalService->getTierPrices($sku)
            ->mapInto(TierPriceData::class);

        $specialPrices = $this->externalService->getSpecialPrices($sku)
            ->mapInto(SpecialPriceData::class);

        return new PriceData($sku, collect([$basePrice]), $tierPrices, $specialPrices);
    }
}
```

Then register your retriever in the config file `config/magento-prices.php`:

```php
<?php

return [
    'retrievers' => [
        'price' => ExamplePriceRetriever::class,
    ],
```

You can retrieve the price by running: `php artisan price:retrieve {sku}`

##### Storing Money

We use Brick/money for storing prices, to create a price use:
```php
 $basePrice = new BasePriceData(
            Money::of(10, config('laravel-magento-prices.currency'))
        );
```

There is a helper for that which adds precision, context and the rounding mode from the config: `JustBetter\MagentoPrices\Helpers\MoneyHelper`

##### Example

To help you get started you can look at the `\JustBetter\MagentoPrices\Retriever\DummyPriceRetriever`

#### SKU Retriever

In order to know what SKU's to retrieve prices for you have to create a SKU retriever class. This class must extend `\JustBetter\MagentoPrices\Retriever\SkuRetriever`.

It is required to have a method that retrieves all skus. You can optionally implement the `retrieveByDate` method to retrieve updated skus.

And example can be found in `\JustBetter\MagentoPrices\Retriever\DummySkuRetriever`

Don't forget to register your retriever in the config file `config/magento-prices.php`:

```php
<?php

return [
    'retrievers' => [
        'sku' => MyAwewsomeSkuRetriever::class,
    ],
```

### Checking for missing prices

There is a build in action that checks all products in Magento where there is no price or the price is zero.
For each product it will automatically start an update or retrieve.

You can run this with the command: `php artisan price:missing`


### Syncing

The `php artisan price:sync` command will check the `retrieve` and `update` flags and dispatch jobs to retrieve/update the prices.
In order to not overload your price source or Magento you can set limits in the config file.
```php
<?php

return [
    /* How many price retrieval jobs may be dispatched per sync */
    'retrieve_limit' => 25,

    /* How many prices update jobs may be dispatched per sync */
    'update_limit' => 100,
];
```

#### Long Waits

The sync limits the amount of products that are retrieved/updated each sync.
This may result in long waits if not properly configured for the amount of updates you get.

To detect this you can add the `\JustBetter\MagentoPrices\Commands\MonitorWaitTimesCommand` to your schedule.
This will fire the `\JustBetter\MagentoPrices\Events\LongWaitDetectedEvent` event in which you can for example trigger more updates or send a notification.

You can configure the limits of when the event will be fired in the config:
```php
<?php

return [
    'monitor' => [
        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'retrieval_max_wait' => 30,

        /* Max wait time in minutes, if exceeded the LongWaitDetected event is dispatched */
        'update_max_wait' => 30,
    ]
];
```

### Handling failures

When an update fails it will try again. A fail counter is stored with the model which is increased at each failure.
A common failure is a missing required attribute in Magento.

In the config you can specify how many times the update may be attempted:
```php
<?php

return [
    /* How many times can a price update failed before being cancelled */
    'fail_count' => 5,
];
```
> *Note* This applies to all three types of updates. Base, tier and special.

## Quality

To ensure the quality of this package, run the following command:

```shell
composer quality
```

This will execute three tasks:

1. Makes sure all tests are passed
2. Checks for any issues using static code analysis
3. Checks if the code is correctly formatted

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vincent Boon](https://github.com/VincentBean)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
