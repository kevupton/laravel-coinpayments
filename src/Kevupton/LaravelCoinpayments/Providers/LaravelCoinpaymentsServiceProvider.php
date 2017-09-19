<?php namespace Kevupton\LaravelCoinpayments\Providers;

use Illuminate\Support\ServiceProvider;

class PackageNameServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../../config/coinpayments.php' => config_path('coinpayments.php')]);

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