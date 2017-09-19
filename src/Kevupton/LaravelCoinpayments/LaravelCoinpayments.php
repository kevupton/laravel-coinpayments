<?php

namespace Kevupton\LaravelCoinpayments;

use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsResponseError;
use Kevupton\LaravelCoinpayments\Models\Log;
use Kevupton\LaravelCoinpayments\Models\Model;
use Kevupton\LaravelCoinpayments\Models\Transaction;
use Kevupton\LaravelCoinpayments\Models\Transfer;
use Kevupton\LaravelCoinpayments\Models\Withdrawal;

/**
 * Class LaravelCoinpayments
 * @package Kevupton\LaravelCoinpayments
 * @method Transaction createTransactionSimple($amount, $currencyIn, $currencyOut, $additional = [])
 * @method Transaction createTransaction($req)
 * @method Transfer createTransfer($amount, $currency, $merchant, $autoConfirm = false)
 * @method Withdrawal createWithdrawal($amount, $currency, $address, $autoConfirm = false, $ipnUrl = '')
 *
 */
class LaravelCoinpayments extends Coinpayments {

    private $app;

    public function __construct($app)
    {
        $this->app = $app;

        parent::__construct(
            cp_conf('private_key'),
            cp_conf('public_key'),
            cp_conf('merchant_id'),
            cp_conf('ipn_secret'),
            cp_conf('ipn_url'),
            cp_conf('format')
        );
    }

    /**
     * Overrides the apiCall function returning the element
     *
     * @param string $cmd
     * @param array $req
     * @return Model
     * @throws CoinPaymentsResponseError
     */
    protected function apiCall($cmd, $req = array())
    {
        $receipt = parent::apiCall($cmd, $req);

        $this->logCall($receipt);

        if ($receipt->hasError())
            throw new CoinPaymentsResponseError($receipt->getError());

        switch ($receipt->getCommand()) {
            case CoinpaymentsCommands::CREATE_TRANSACTION:
                return Transaction::create($receipt->toResultArray());
            case CoinpaymentsCommands::CREATE_WITHDRAWAL:
                return Withdrawal::create($receipt->toResultArray());
            case CoinpaymentsCommands::CREATE_TRANSFER:
                return Transfer::create($receipt->toResultArray());
        }

        return $receipt->getResponse()->result;

    }

    /**
     * Logs the call depending on the log level of the application
     * @param Receipt $receipt
     */
    private function logCall (Receipt $receipt) {

        // check if this request should be logged
        if (!(CP_LOG_LEVEL === Log::LEVEL_ALL ||
            CP_LOG_LEVEL === Log::LEVEL_ERROR && $receipt->hasError())) return;

        cp_log(json_encode([
            'request' => $receipt->getRequest(),
            'response' => $receipt->getResponse()
        ]), $receipt->hasError() ? 'ERROR' : null);

    }
}