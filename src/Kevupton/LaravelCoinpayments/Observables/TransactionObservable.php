<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 24/03/2018
 * Time: 6:17 PM
 */

namespace Kevupton\LaravelCoinpayments\Observables;

use Kevupton\LaravelCoinpayments\Enums\IpnStatus;
use Kevupton\LaravelCoinpayments\Events\Transaction\TransactionComplete;
use Kevupton\LaravelCoinpayments\Events\Transaction\TransactionCreated;
use Kevupton\LaravelCoinpayments\Events\Transaction\TransactionUpdated;
use Kevupton\LaravelCoinpayments\Models\Transaction;

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