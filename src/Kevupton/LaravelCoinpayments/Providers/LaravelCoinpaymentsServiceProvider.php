<?php namespace Kevupton\LaravelCoinpayments\Providers;

use Kevupton\LaravelCoinpayments\Controllers\CoinpaymentsController;
use Kevupton\LaravelCoinpayments\Facades\Coinpayments;
use Kevupton\LaravelCoinpayments\LaravelCoinpayments;
use Kevupton\LaravelCoinpayments\Models\Deposit;
use Kevupton\LaravelCoinpayments\Models\Transaction;
use Kevupton\LaravelCoinpayments\Models\Withdrawal;
use Kevupton\LaravelCoinpayments\Observables\DepositObservable;
use Kevupton\LaravelCoinpayments\Observables\TransactionObservable;
use Kevupton\LaravelCoinpayments\Observables\WithdrawalObservable;
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

        Deposit::observe(new DepositObservable());
        Withdrawal::observe(new WithdrawalObservable());
        Transaction::observe(new TransactionObservable());
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
        $this->registerRoute();

        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/coinpayments.php', 'coinpayments'
        );
    }

    private function registerRoute ()
    {
        $is_enabled = config('coinpayments.route.enabled');
        $path       = config('coinpayments.route.path');

        var_dump($is_enabled);
        die();
        if (!$is_enabled) {
            return;
        }

        $router = $this->router();
        $router->post($path, ['as' => 'coinpayments.ipn', 'uses' => CoinpaymentsController::class . '@validateIPN']);
    }
}