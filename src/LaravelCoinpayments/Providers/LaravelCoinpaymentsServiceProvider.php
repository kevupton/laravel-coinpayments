<?php namespace Oasin\LaravelCoinpayments\Providers;

use Oasin\LaravelCoinpayments\Controllers\CoinpaymentsController;
use Oasin\LaravelCoinpayments\Facades\Coinpayments;
use Oasin\LaravelCoinpayments\LaravelCoinpayments;
use Oasin\LaravelCoinpayments\Models\Deposit;
use Oasin\LaravelCoinpayments\Models\Transaction;
use Oasin\LaravelCoinpayments\Models\Withdrawal;
use Oasin\LaravelCoinpayments\Observables\DepositObservable;
use Oasin\LaravelCoinpayments\Observables\TransactionObservable;
use Oasin\LaravelCoinpayments\Observables\WithdrawalObservable;
use Oasin\LaravelPackageServiceProvider\ServiceProvider;

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

        if (!$is_enabled) {
            return;
        }

        $router = $this->router();
        $router->post($path, ['as' => 'coinpayments.ipn', 'uses' => CoinpaymentsController::class . '@validateIPN']);
    }
}