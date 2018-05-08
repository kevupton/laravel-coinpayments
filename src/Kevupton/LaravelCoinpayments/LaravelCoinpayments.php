<?php

namespace Kevupton\LaravelCoinpayments;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Kevupton\LaravelCoinpayments\Enums\CoinpaymentsCommand;
use Kevupton\LaravelCoinpayments\Enums\IpnType;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsException;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsResponseError;
use Kevupton\LaravelCoinpayments\Exceptions\IpnIncompleteException;
use Kevupton\LaravelCoinpayments\Models\CallbackAddress;
use Kevupton\LaravelCoinpayments\Models\Conversion;
use Kevupton\LaravelCoinpayments\Models\Deposit;
use Kevupton\LaravelCoinpayments\Models\Ipn;
use Kevupton\LaravelCoinpayments\Models\Log;
use Kevupton\LaravelCoinpayments\Models\MassWithdrawal;
use Kevupton\LaravelCoinpayments\Models\Model;
use Kevupton\LaravelCoinpayments\Models\Transaction;
use Kevupton\LaravelCoinpayments\Models\Transfer;
use Kevupton\LaravelCoinpayments\Models\Withdrawal;

/**
 * Class LaravelCoinpayments
 *
 * @package Kevupton\LaravelCoinpayments
 * @method Transaction createTransactionSimple($amount, $currencyIn, $currencyOut, $additional = [])
 * @method Transaction createTransaction($req)
 * @method Transfer createTransfer($amount, $currency, $merchant, $autoConfirm = false)
 * @method Withdrawal createWithdrawal($amount, $currency, $address, $autoConfirm = false, $ipnUrl = '')
 *
 */
class LaravelCoinpayments extends Coinpayments
{

    private $app;

    public function __construct ($app)
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
     * @param array  $req
     * @return Model
     * @throws CoinPaymentsException
     * @throws CoinPaymentsResponseError
     * @throws Exceptions\JsonParseException
     * @throws Exceptions\MessageSendException
     */
    protected function apiCall ($cmd, $req = [])
    {
        $receipt = parent::apiCall($cmd, $req);

        $has_error = $receipt->hasError();

        cp_log([
            'request'  => $receipt->getRequest(),
            'response' => $receipt->getResponse(),
        ], $has_error ? 'API_CALL_ERROR' : 'API_CALL',
            $has_error ? Log::LEVEL_ERROR : Log::LEVEL_ALL
        );

        if ($has_error) {
            throw new CoinPaymentsResponseError($receipt->getError());
        }

        $data = $receipt->toArray();

        if (isset($data['id'])) {
            $data['ref_id'] = $data['id'];
            unset($data['id']);
        }

        switch ($receipt->getCommand()) {
            case CoinpaymentsCommand::CREATE_TRANSACTION:
                // the currency1 we sent to coinpayments
                $data['amount1'] = $req['amount'];
                // the currency2 coinpayments returned to us
                $data['amount2'] = $data['amount'];
                return Transaction::create($data);
            case CoinpaymentsCommand::CREATE_WITHDRAWAL:
                return $this->saveWithdrawal($receipt->getResponse()['result'], $receipt->getRequest());
            case CoinpaymentsCommand::CREATE_TRANSFER:
                return Transfer::create($data);
            case CoinpaymentsCommand::GET_CALLBACK_ADDRESS:
                return CallbackAddress::create($data);
            case CoinpaymentsCommand::CONVERT:
                return Conversion::create($data);
            case CoinpaymentsCommand::CREATE_MASS_WITHDRAWAL:
                return $this->registerMassWithdrawal($receipt);
        }

        return $receipt->getResponse()['result'];
    }

    /**
     *
     * @param Receipt $receipt
     * @return MassWithdrawal
     */
    private function registerMassWithdrawal (Receipt $receipt)
    {
        /** @var MassWithdrawal $mass_withdrawal */
        $mass_withdrawal = MassWithdrawal::create();

        $requests = [];
        collect($receipt->getRequest())->filter(function ($value, $key) {
            return preg_match('/^wd\[wd/ui', $key);
        })->each(function ($value, $key) use ($requests) {
            if (preg_match('/^wd\[wd([0-9]+)\]\[(.*?)\]/ui', $key, $matches)) {
                $index = intval($matches[1]);
                if (!isset($requests[$index])) {
                    $requests[$index] = [];
                }
                $requests[$index][$matches[2]] = $value;
            }
        });

        $mass_withdrawal->withdrawals = collect($receipt->getResponse()['result'])
            ->map(function ($value, $index) use ($mass_withdrawal, $requests) {
                return $this->saveWithdrawal($value, $requests[$index], $mass_withdrawal->id);
            });

        return $mass_withdrawal;
    }

    /**
     * Saves the withdrawal from the request and response data.
     *
     * @param null $result
     * @param null $request
     * @param null $mass_withdrawal_id
     * @return mixed
     */
    private function saveWithdrawal ($result  = null, $request = null, $mass_withdrawal_id = null)
    {
        if (isset($result['id'])) {
            $result['ref_id'] = $result['id'];
            unset($result['id']);
        }

        if (isset($result['currency2'])) {
            $result['amount2'] = $result['amount'];
            unset($result['amount']);
        }

        $data = array_merge($request, $result, ['mass_withdrawal_id' => $mass_withdrawal_id]);

        return Withdrawal::create($data);
    }

    /**
     * @param array      $request
     * @param array|null $server
     * @param array      $headers
     * @return Ipn
     * @throws IpnIncompleteException|CoinPaymentsException
     */
    public function validateIPN (array $request, array $server, $headers = [])
    {
        $log_data = [
            'request' => $request,
            'headers' => $headers,
            'server'  => array_intersect_key($server, [
                'PHP_AUTH_USER' => '',
                'PHP_AUTH_PW'   => '',
                'HTTP_HMAC'     => '',
            ]),
        ];

        try {
            cp_log($log_data, 'IPN_RECEIVED', Log::LEVEL_ALL);

            $is_complete = parent::validateIPN($request, $server);

            try {
                $ipn = Ipn::where('ipn_id', $request['ipn_id'])->firstOrFail();
            } catch (\Exception $e) {
                $ipn = new Ipn();
            }

            $ipn->fill($request);
            $ipn->save();

            $this->updateModel($ipn);

            // only return the ipn if it was successful, otherwise throw an exception
            // we do it like this so we can record the ipn either way.
            if ($is_complete) {
                return $ipn;
            } else {
                throw new IpnIncompleteException($request['status_text'], $ipn);
            }
        } catch (CoinPaymentsException $e) {
            $log_data['error_message'] = $e->getMessage();

            cp_log($log_data, 'IPN_ERROR', Log::LEVEL_ERROR);

            throw $e;
        }
    }

    /**
     * @param Ipn $ipn
     * @throws CoinPaymentsException
     */
    private function updateModel (Ipn $ipn)
    {
        // create or update the existing IPN record
        try {
            $ipn_type = $ipn->ipn_type;
            $txn_id   = $ipn->txn_id;
        } catch (\Exception $e) {
            throw new CoinPaymentsException('Invalid coinpayments IPN. Missing an ipn_type or txn_id');
        }

        $condition = ['txn_id' => $txn_id];
        switch ($ipn_type) {
            case IpnType::DEPOSIT:
                Deposit::updateOrCreate($condition, $this->filterNullable($ipn->toArray()));
                break;
            case IpnType::API:
                Transaction::updateOrCreate($condition, $this->filterNullable($ipn->toArray()));
                break;
            case IpnType::WITHDRAW:
                Withdrawal::updateOrCreate($condition, $this->filterNullable($ipn->toArray()));
                break;
        }
    }

    /**
     * Removes all of the nullable values from an array.
     * This way the values wont overwrite existing possible values.
     *
     * @param $array
     * @return array
     */
    private function filterNullable ($array)
    {
        return collect($array)->filter(function ($value) {
            return !is_null($value);
        })->toArray();
    }

    /**
     * @param Request $request
     * @return Ipn
     */
    public function validateIPNRequest (Request $request)
    {
        return $this->validateIPN($request->all(), $request->server(), $request->headers);
    }
}