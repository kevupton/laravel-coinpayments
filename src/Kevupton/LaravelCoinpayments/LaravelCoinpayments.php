<?php

namespace Kevupton\LaravelCoinpayments;

use Illuminate\Http\Request;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsResponseError;
use Kevupton\LaravelCoinpayments\Models\Ipn;
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

        $has_error = $receipt->hasError();

        cp_log([
            'request' => $receipt->getRequest(),
            'response' => $receipt->getResponse()
        ], $has_error ? 'API_CALL_ERROR' : 'API_CALL',
            $has_error ? Log::LEVEL_ERROR : Log::LEVEL_ALL
        );

        if ($has_error)
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
     * @param array $request
     * @param array|null $server
     * @return Ipn
     * @throws \Exception
     */
    public function validateIPN(array $request, array $server)
    {
        try {
            parent::validateIPN($request, $server);
        }
        catch (\Exception $e) {
            cp_log([
                'error_message' => $e->getMessage(),
                'request_content' => $request->all(),
                'request_headers' => $request->headers,
                'server' => array_intersect_key($request->server(), [
                    'PHP_AUTH_USER', 'PHP_AUTH_PW'
                ])
            ], 'IPN_ERROR', Log::LEVEL_ERROR);

            throw $e;
        }

        return Ipn::create($request->all());
    }

    /**
     * @param Request $request
     * @return Ipn
     */
    public function validateIPNRequest (Request $request) {
        return $this->validateIPN($request->all(), $request->server());
    }
}