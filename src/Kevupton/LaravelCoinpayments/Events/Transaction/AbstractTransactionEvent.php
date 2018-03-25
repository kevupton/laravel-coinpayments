<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 25/03/2018
 * Time: 9:46 PM
 */

namespace Kevupton\LaravelCoinpayments\Events\Transaction;

use App\Events\Event;
use Kevupton\LaravelCoinpayments\Models\Transaction;

class AbstractTransactionEvent extends Event
{
    /**
     * @var Transaction
     */
    private $transaction;

    public function __construct (Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}