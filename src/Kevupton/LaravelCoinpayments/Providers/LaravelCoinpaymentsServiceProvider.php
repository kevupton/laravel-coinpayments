<?php namespace Kevupton\LaravelCoinpayments\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Kevupton\LaravelCoinpayments\Facades\Coinpayments;
use Kevupton\LaravelCoinpayments\LaravelCoinpayments;

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
        $this->publishes([__DIR__ . '/../../../config/coinpayments.php' => config_path('coinpayments.php')]);

        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register ()
    {
        $app = app();

        $app->singleton(self::SINGLETON, function ($app) {
            return new LaravelCoinpayments($app);
        });

        if (is_a($app, 'Illuminate\Foundation\Application')) {
            AliasLoader::getInstance()->alias('Coinpayments', Coinpayments::class);
        } elseif (is_a($app, 'Laravel\Lumen\Application')) {
            if (!class_exists('Coinpayments')) {
                class_alias(Coinpayments::class, 'Coinpayments');
            }
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/coinpayments.php', 'coinpayments'
        );
    }
}