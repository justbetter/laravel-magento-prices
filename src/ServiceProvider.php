<?php

namespace JustBetter\MagentoPrices;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JustBetter\MagentoAsync\Events\BulkOperationStatusEvent;
use JustBetter\MagentoPrices\Actions\ProcessPrices;
use JustBetter\MagentoPrices\Actions\Retrieval\RetrieveAllPrices;
use JustBetter\MagentoPrices\Actions\Retrieval\RetrievePrice;
use JustBetter\MagentoPrices\Actions\Retrieval\SavePrice;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdateBasePricesAsync;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdatePricesAsync;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdateSpecialPricesAsync;
use JustBetter\MagentoPrices\Actions\Update\Async\UpdateTierPricesAsync;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateBasePrice;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdatePrice;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateSpecialPrice;
use JustBetter\MagentoPrices\Actions\Update\Sync\UpdateTierPrice;
use JustBetter\MagentoPrices\Actions\Utility\CheckTierDuplicates;
use JustBetter\MagentoPrices\Actions\Utility\DeleteCurrentSpecialPrices;
use JustBetter\MagentoPrices\Actions\Utility\FilterTierPrices;
use JustBetter\MagentoPrices\Actions\Utility\ImportCustomerGroups;
use JustBetter\MagentoPrices\Actions\Utility\ProcessProductsWithMissingPrices;
use JustBetter\MagentoPrices\Actions\Utility\RetrieveCustomerGroups;
use JustBetter\MagentoPrices\Commands\ProcessPricesCommand;
use JustBetter\MagentoPrices\Commands\Retrieval\RetrieveAllPricesCommand;
use JustBetter\MagentoPrices\Commands\Retrieval\RetrievePriceCommand;
use JustBetter\MagentoPrices\Commands\Update\UpdateAllPricesCommand;
use JustBetter\MagentoPrices\Commands\Update\UpdatePriceCommand;
use JustBetter\MagentoPrices\Commands\Utility\ImportCustomerGroupsCommand;
use JustBetter\MagentoPrices\Commands\Utility\ProcessProductsWithMissingPricesCommand;
use JustBetter\MagentoPrices\Listeners\BulkOperationStatusListener;
use JustBetter\MagentoPrices\Listeners\ProductDataModifiedListener;
use JustBetter\MagentoProducts\Events\ProductDataModifiedEvent;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this
            ->registerConfig()
            ->registerActions();
    }

    protected function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magento-prices.php', 'magento-prices');

        return $this;
    }

    protected function registerActions(): static
    {
        RetrieveAllPrices::bind();
        RetrievePrice::bind();
        SavePrice::bind();

        UpdatePricesAsync::bind();
        UpdateBasePricesAsync::bind();
        UpdateTierPricesAsync::bind();
        UpdateSpecialPricesAsync::bind();

        UpdatePrice::bind();
        UpdateBasePrice::bind();
        UpdateTierPrice::bind();
        UpdateSpecialPrice::bind();

        ProcessPrices::bind();

        DeleteCurrentSpecialPrices::bind();
        RetrieveCustomerGroups::bind();
        CheckTierDuplicates::bind();
        ProcessProductsWithMissingPrices::bind();
        ImportCustomerGroups::bind();
        FilterTierPrices::bind();

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootMigrations()
            ->bootConfig()
            ->bootCommands()
            ->bootListeners();
    }

    protected function bootConfig(): static
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/magento-prices.php' => config_path('magento-prices.php'),
            ], 'config');
        }

        return $this;
    }

    protected function bootCommands(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RetrieveAllPricesCommand::class,
                RetrievePriceCommand::class,
                UpdateAllPricesCommand::class,
                UpdatePriceCommand::class,
                ProcessPricesCommand::class,
                ImportCustomerGroupsCommand::class,
                ProcessProductsWithMissingPricesCommand::class,
            ]);
        }

        return $this;
    }

    protected function bootMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }

    protected function bootListeners(): static
    {
        Event::listen(BulkOperationStatusEvent::class, BulkOperationStatusListener::class);
        Event::listen(ProductDataModifiedEvent::class, ProductDataModifiedListener::class);

        return $this;
    }
}
