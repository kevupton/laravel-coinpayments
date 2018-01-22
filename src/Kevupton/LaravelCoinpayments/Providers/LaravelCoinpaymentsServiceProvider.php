<?php namespace Kevupton\LaravelCoinpayments\Providers;

use Kevupton\LaravelCoinpayments\Facades\Coinpayments;
use Kevupton\LaravelCoinpayments\LaravelCoinpayments;
use Kevupton\LaravelPackageServiceProvider\ServiceProvider;

class LaravelCoinpaymentsServiceProvider extends ServiceProvider
{

    const SINGLETON = 'coinpayments';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot ()
    {
        $this->registerConfig(__DIR__ . '/../../../config/coinpayments.php', 'coinpayments.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register ()
    {
        $this->app->singleton(self::SINGLETON, function ($app) {
            return new LaravelCoinpayments($app);
        });

        $this->registerAlias(Coinpayments::class, 'Coinpayments');

        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/coinpayments.php', 'coinpayments'
        );
    }
}