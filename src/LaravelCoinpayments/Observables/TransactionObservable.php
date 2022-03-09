<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 24/03/2018
 * Time: 6:17 PM
 */

namespace Oasin\LaravelCoinpayments\Observables;

use Oasin\LaravelCoinpayments\Enums\IpnStatus;
use Oasin\LaravelCoinpayments\Events\Transaction\TransactionComplete;
use Oasin\LaravelCoinpayments\Events\Transaction\TransactionCreated;
use Oasin\LaravelCoinpayments\Events\Transaction\TransactionUpdated;
use Oasin\LaravelCoinpayments\Models\Transaction;

class TransactionObservable
{
    public function updated (Transaction $transaction)
    {
        event(new TransactionUpdated($transaction));
        $this->checkStatus($transaction);
    }

    public function created (Transaction $transaction)
    {
        event(new TransactionCreated($transaction));
        $this->checkStatus($transaction);
    }

    private function checkStatus (Transaction $transaction)
    {
        if (IpnStatus::isComplete($transaction->status)) {
            event(new TransactionComplete($transaction));
        }
    }
}