<?php

namespace JustBetter\MagentoPrices;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JustBetter\MagentoPrices\Actions\CheckTierDuplicates;
use JustBetter\MagentoPrices\Actions\DeterminePricesEqual;
use JustBetter\MagentoPrices\Actions\FindProductsWithMissingPrices;
use JustBetter\MagentoPrices\Actions\ImportGroups;
use JustBetter\MagentoPrices\Actions\MonitorWaitTimes;
use JustBetter\MagentoPrices\Actions\ProcessPrice;
use JustBetter\MagentoPrices\Actions\SyncPrices;
use JustBetter\MagentoPrices\Actions\UpdateMagentoBasePrice;
use JustBetter\MagentoPrices\Actions\UpdateMagentoSpecialPrices;
use JustBetter\MagentoPrices\Actions\UpdateMagentoTierPrices;
use JustBetter\MagentoPrices\Commands\ImportGroupsCommand;
use JustBetter\MagentoPrices\Commands\MonitorWaitTimesCommand;
use JustBetter\MagentoPrices\Commands\RetrievePricesCommand;
use JustBetter\MagentoPrices\Commands\SearchMissingPricesCommand;
use JustBetter\MagentoPrices\Commands\SyncPricesCommand;
use JustBetter\MagentoPrices\Commands\UpdatePriceCommand;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magento-prices.php', 'magento-prices');

        $this->registerActions();
    }

    protected function registerActions(): static
    {
        UpdateMagentoBasePrice::bind();
        UpdateMagentoSpecialPrices::bind();
        UpdateMagentoTierPrices::bind();

        FindProductsWithMissingPrices::bind();
        CheckTierDuplicates::bind();

        SyncPrices::bind();
        ProcessPrice::bind();

        MonitorWaitTimes::bind();
        DeterminePricesEqual::bind();

        ImportGroups::bind();

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootMigrations()
            ->bootConfig()
            ->bootCommands();
    }

    protected function bootConfig(): self
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/magento-prices.php' => config_path('magento-prices.php'),
            ], 'config');
        }

        return $this;
    }

    protected function bootCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportGroupsCommand::class,
                RetrievePricesCommand::class,
                SyncPricesCommand::class,
                UpdatePriceCommand::class,
                SearchMissingPricesCommand::class,
                MonitorWaitTimesCommand::class,
            ]);
        }

        return $this;
    }

    protected function bootMigrations(): self
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }
}
