<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 1:14 PM
 */

namespace Oasin\LaravelCoinpayments\Facades;

use Illuminate\Support\Facades\Facade;
use Oasin\LaravelCoinpayments\Providers\LaravelCoinpaymentsServiceProvider;

class Coinpayments extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return LaravelCoinpaymentsServiceProvider::SINGLETON; }
}