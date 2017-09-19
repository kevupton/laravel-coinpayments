<?php namespace Kevupton\LaravelCoinpayments\Providers;

use Illuminate\Support\ServiceProvider;
use Kevupton\LaravelCoinpayments\Coinpayments;

class PackageNameServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../../config/coinpayments.php' => config_path('coinpayments.php')]);

        app()->singleton('coinpayments', function ($app) {
           return new Coinpayments(
               cp_conf('private_key'),
               cp_conf('public_key'),
               cp_conf('merchant_id'),
               cp_conf('ipn_secret'),
               cp_conf('ipn_url'),
               cp_conf('format')
           );
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