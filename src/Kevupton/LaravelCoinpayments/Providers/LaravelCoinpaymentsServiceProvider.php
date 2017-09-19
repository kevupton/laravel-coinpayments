<?php namespace Kevupton\LaravelCoinpayments\Providers;

use Illuminate\Support\ServiceProvider;
use Kevupton\LaravelCoinpayments\Coinpayments;
use Kevupton\LaravelCoinpayments\LaravelCoinpayments;

class LaravelCoinpaymentsServiceProvider extends ServiceProvider {

    const SINGLETON = 'coinpayments';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../../config/coinpayments.php' => config_path('coinpayments.php')]);

        app()->singleton(self::SINGLETON, function ($app) {
           return new LaravelCoinpayments($app);
        });

        $this->loadMigrationsFrom(__DIR__ . '../../../database/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
           __DIR__ . '../../../config/coinpayments.php', 'coinpayments'
        );
    }
}