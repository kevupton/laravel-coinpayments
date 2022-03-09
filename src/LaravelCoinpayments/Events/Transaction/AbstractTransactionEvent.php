<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 25/03/2018
 * Time: 9:46 PM
 */

namespace Oasin\LaravelCoinpayments\Events\Transaction;

use Oasin\LaravelCoinpayments\Events\Event;
use Oasin\LaravelCoinpayments\Models\Transaction;

class AbstractTransactionEvent extends Event
{
    /**
     * @var Transaction
     */
    public $transaction;

    public function __construct (Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}