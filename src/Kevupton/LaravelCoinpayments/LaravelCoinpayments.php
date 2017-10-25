<?php

namespace Kevupton\LaravelCoinpayments;

use Illuminate\Http\Request;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsException;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsResponseError;
use Kevupton\LaravelCoinpayments\Exceptions\IpnIncompleteException;
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

        $data = $receipt->toResultArray();

        if (isset($data['id'])) {
            $data['ref_id'] = $data['id'];
            unset($data['id']);
        }

        switch ($receipt->getCommand()) {
            case CoinpaymentsCommands::CREATE_TRANSACTION:
                return Transaction::create($data);
            case CoinpaymentsCommands::CREATE_WITHDRAWAL:
                return Withdrawal::create($data);
            case CoinpaymentsCommands::CREATE_TRANSFER:
                return Transfer::create($data);
        }

        return $receipt->getResponse()['result'];

    }

    /**
     * @param array $request
     * @param array|null $server
     * @param array $headers
     * @return Ipn
     * @throws IpnIncompleteException|CoinPaymentsException
     */
    public function validateIPN(array $request, array $server, $headers = [])
    {
        $log_data = [
            'request' => $request,
            'headers' => $headers,
            'server' => array_intersect_key($server, [
                'PHP_AUTH_USER', 'PHP_AUTH_PW'
            ])
        ];

        try {
            cp_log($log_data, 'IPN_RECEIVED', Log::LEVEL_ALL);

            $is_complete    = parent::validateIPN($request, $server);

            // create or update the existing IPN record
            try {
                $ipn = Ipn::where('ipn_id', $request['ipn_id'])->firstOrFail();
            }
            catch (\Exception $e) {
                $ipn = new Ipn();
            }

            $ipn->fill($request);
            $ipn->save();

            // only return the ipn if it was successful, otherwise throw an exception
            // we do it like this so we can record the ipn either way.
            if ($is_complete) {
                return $ipn;
            } else {
                throw new IpnIncompleteException($request['status_text'], $ipn);
            }
        }
        catch (CoinPaymentsException $e) {
            $log_data['error_message'] = $e->getMessage();

            cp_log($log_data, 'IPN_ERROR', Log::LEVEL_ERROR);

            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return Ipn
     */
    public function validateIPNRequest (Request $request) {
        return $this->validateIPN($request->all(), $request->server(), $request->headers);
    }
}