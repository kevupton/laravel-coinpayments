<?php namespace Kevupton\Referrals\Providers;

use Illuminate\Support\ServiceProvider;

class PackageNameServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../../config/config.php' => config_path('bootstrap-config.php')]);
        // for migrations
//        $this->publishes([
//            __DIR__.'/../../../database/migrations/' => database_path('migrations')
//        ], 'migrations');
        // for seeding
//        $this->publishes([
//            __DIR__.'/../../../database/seeds/' => database_path('seeds')
//        ], 'seeds');

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
//
    }
}