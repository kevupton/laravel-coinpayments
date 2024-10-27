<?php

namespace Kevupton\LaravelCoinpayments;

use Illuminate\Validation\ValidationException;
use Kevupton\LaravelCoinpayments\Enums\CoinpaymentsCommand;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsException;
use Kevupton\LaravelCoinpayments\Exceptions\JsonParseException;
use Kevupton\LaravelCoinpayments\Exceptions\MessageSendException;
use Kevupton\LaravelCoinpayments\Models\Log;

class Coinpayments
{

    private $merchant_id;
    private $ipn_secret;
    private $private_key;
    private $public_key;
    private $ipn_url;
    private $format;
    private $ch = null;

    public function __construct($private_key, $public_key, $merchant_id = null, $ipn_secret = null, $ipn_url = null, $format = 'json')
    {
        $this->merchant_id = $merchant_id;
        $this->public_key = $public_key;
        $this->private_key = $private_key;
        $this->ipn_secret = $ipn_secret;
        $this->ipn_url = $ipn_url;
        $this->format = $format;
    }


    public function setParameter($key, $value = null)
    {
        $this->$key = $value;
        return $this;
    }


    public function getParameter($key)
    {
        return $this->$key;
    }


    public function setIPNSecret($value)
    {
        return $this->setParameter('ipn_secret', $value);
    }


    public function getIPNSecret()
    {
        return $this->getParameter('ipn_secret');


    }

    public function setMerchantID($value)
    {
        return $this->setParameter('merchant_id', $value);
    }

    public function getMerchantID()
    {
        return $this->getParameter('merchant_id');
    }

    public function setIPN($value)
    {
        return $this->setParameter('ipn_url', $value);
    }


    public function setPrivateKey($value)
    {
        return $this->setParameter('private_key', $value);
    }

    public function setPublicKey($value)
    {
        return $this->setParameter('public_key', $value);
    }


    public function getPrivateKey()
    {
        return $this->getParameter('private_key');
    }

    public function getPublicKey()
    {
        return $this->getParameter('public_key');
    }

    /**
     * Gets the current CoinPayments.net exchange rate. Output includes both crypto and fiat currencies.
     *
     * @param bool $short short == true (the default), the output won't include the currency names and confirms needed
     *                    to save bandwidth.
     * @param bool $accepted
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getRates ($short = true, $accepted = true)
    {
        return $this->apiCall(CoinpaymentsCommand::RATES, ['short' => (int)$short, 'accepted' => (int)$accepted]);
    }

    /**
     * Gets your current coin balances (only includes coins with a balance unless all = true).<br />
     *
     * @param bool $all all = true then it will return all coins, even those with a 0 balance.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getBalances ($all = false)
    {
        return $this->apiCall(CoinpaymentsCommand::BALANCES, ['all' => $all ? 1 : 0]);
    }

    /**
     * Creates a basic transaction with minimal parameters.<br />
     * See CreateTransaction for more advanced features.
     *
     * @param mixed  $amount      The amount of the transaction (floating point to 8 decimals).
     * @param string $currencyIn  The source currency (ie. USD), this is used to calculate the exchange rate for you.
     * @param string $currencyOut The cryptocurrency of the transaction. currency1 and currency2 can be the same if you
     *                            don't want any exchange rate conversion.
     * @param array  $additional  Optionally set additional fields.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function createTransactionSimple ($amount, $currencyIn, $currencyOut, $additional = [])
    {
        $acceptableFields = [
            'address', 'buyer_email', 'buyer_name',
            'item_name', 'item_number', 'invoice', 'custom', 'ipn_url',
        ];

        $request = [
            'amount'    => $amount,
            'currency1' => $currencyIn,
            'currency2' => $currencyOut,
        ];

        foreach ($acceptableFields as $field) {
            if (isset($additional[$field])) {
                $request[$field] = $additional[$field];
            }
        }

        return $this->apiCall(CoinpaymentsCommand::CREATE_TRANSACTION, $request);
    }

    /**
     * @param $req
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function createTransaction ($req)
    {
        // See https://www.coinpayments.net/apidoc-create-transaction for parameters
        return $this->apiCall(CoinpaymentsCommand::CREATE_TRANSACTION, $req);
    }

    /**
     * @param $amount
     * @param $from
     * @param $to
     * @param $address
     * @return Receipt
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function convertCoins ($amount, $from, $to, $address = null)
    {
        $req = [
            'amount'  => $amount,
            'from'    => $from,
            'to'      => $to,
            'address' => $address,
        ];
        return $this->apiCall(CoinpaymentsCommand::CONVERT, $req);
    }

    /**
     * @param $withdrawals
     * @return Receipt
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function createMassWithdrawal (array $withdrawals)
    {
        $req = collect($withdrawals)->flatMap(function ($withdrawal, $index) {
            // the minimum required values for it to work.
            app('validator')->validate($withdrawal, [
                'amount'   => 'required|numeric',
                'address'  => 'required|string',
                'currency' => 'required|string',
            ]);

            return collect($withdrawal)->flatMap(function ($value, $key) use ($index) {
               return ["wd[wd$index][$key]" => $value];
            })->toArray();
        })->toArray();

        return $this->apiCall(CoinpaymentsCommand::CREATE_MASS_WITHDRAWAL, $req);
    }

    /**
     * Get transaction information via transaction ID
     *
     * @param string $txID
     * @param bool   $all
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getTransactionInfo ($txID, $all = true)
    {
        $req = [
            'txid' => $txID,
            'full' => (int)$all,
        ];

        return $this->apiCall(CoinpaymentsCommand::GET_TX_INFO, $req);
    }

    /**
     * Creates an address for receiving payments into your CoinPayments Wallet.<br />
     *
     * @param string $currency The cryptocurrency to create a receiving address for.
     * @param string $ipnUrl   Optionally set an IPN handler to receive notices about this transaction. If ipn_url is
     *                         empty then it will use the default IPN URL in your account.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getCallbackAddress ($currency, $ipnUrl = '')
    {
        $req = [
            'currency' => $currency,
            'ipn_url'  => $ipnUrl,
        ];

        return $this->apiCall(CoinpaymentsCommand::GET_CALLBACK_ADDRESS, $req);
    }
    
     /**
     * Generates deposit address<br />
     *
     * @param string $currency The cryptocurrency to create a receiving address for.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getDepositAddress(string $currency) {
         $req = [
            'currency' => $currency,
          //  'ipn_url'  => $ipnUrl,
        ];

        return $this->apiCall(CoinpaymentsCommand::GET_DEPOSIT_ADDRESS, $req);
    }

    /**
     * Creates a withdrawal from your account to a specified address.<br />
     *
     * @param number $amount      The amount of the transaction (floating point to 8 decimals).
     * @param string $currency    The cryptocurrency to withdraw.
     * @param string $address     The address to send the coins to.
     * @param bool   $autoConfirm If auto_confirm is true, then the withdrawal will be performed without an email
     *                            confirmation.
     * @param string $ipnUrl      Optionally set an IPN handler to receive notices about this transaction. If ipn_url
     *                            is empty then it will use the default IPN URL in your account.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function createWithdrawal ($amount, $currency, $address, $autoConfirm = false, $ipnUrl = '')
    {
        $req = [
            'amount'       => $amount,
            'currency'     => $currency,
            'address'      => $address,
            'auto_confirm' => $autoConfirm ? 1 : 0,
            'ipn_url'      => $ipnUrl,
        ];

        return $this->apiCall(CoinpaymentsCommand::CREATE_WITHDRAWAL, $req);
    }

    /**
     * Get withdrawal information via withdrawal ID
     *
     * @param string $refID the withdrawal ID
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getWithdrawalInfo ($refID)
    {
        $req = [
            'id' => $refID
        ];

        return $this->apiCall(CoinpaymentsCommand::GET_WITHDRAWAL_INFO, $req);
    }

    /**
     * Get withdrawal information via withdrawal ID
     *
     * @param string $refID the conversion ID
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getConversionInfo ($refID)
    {
        $req = [
            'id' => $refID
        ];

        return $this->apiCall(CoinpaymentsCommand::GET_CONVERSION_INFO, $req);
    }

    /**
     * Creates a transfer from your account to a specified merchant.<br />
     *
     * @param number $amount      The amount of the transaction (floating point to 8 decimals).
     * @param string $currency    The cryptocurrency to withdraw.
     * @param string $merchant    The merchant ID to send the coins to.
     * @param bool   $autoConfirm If auto_confirm is true, then the transfer will be performed without an email
     *                            confirmation.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function createTransfer ($amount, $currency, $merchant, $autoConfirm = false)
    {
        $req = [
            'amount'       => $amount,
            'currency'     => $currency,
            'merchant'     => $merchant,
            'auto_confirm' => $autoConfirm ? 1 : 0,
        ];

        return $this->apiCall(CoinpaymentsCommand::CREATE_TRANSFER, $req);
    }

    /**
     * Creates a transfer from your account to a specified $PayByName tag.<br />
     *
     * @param number $amount      The amount of the transaction (floating point to 8 decimals).
     * @param string $currency    The cryptocurrency to withdraw.
     * @param string $pbntag      The $PayByName tag to send funds to.
     * @param bool   $autoConfirm If auto_confirm is true, then the transfer will be performed without an email
     *                            confirmation.
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function sendToPayByName ($amount, $currency, $pbntag, $autoConfirm = false)
    {
        $req = [
            'amount'       => $amount,
            'currency'     => $currency,
            'pbntag'       => $pbntag,
            'auto_confirm' => $autoConfirm ? 1 : 0,
        ];

        return $this->apiCall(CoinpaymentsCommand::CREATE_TRANSFER, $req);
    }

    /**
     * Validate the IPN request and payment.
     *
     * @param  array $post_data
     * @param  array $server_data
     * @return mixed
     * @throws CoinPaymentsException
     */
    public function validateIPN (array $post_data, array $server_data)
    {
        if (!isset($post_data['ipn_mode'], $post_data['merchant'], $post_data['status'], $post_data['status_text'])) {
            throw new CoinPaymentsException("Insufficient POST data provided.");
        }

        if ($post_data['ipn_mode'] == 'httpauth') {

            if ($server_data['PHP_AUTH_USER'] !== $this->merchant_id) {
                throw new CoinPaymentsException("Invalid merchant ID provided.");
            }
            if ($server_data['PHP_AUTH_PW'] !== $this->ipn_secret) {
                throw new CoinPaymentsException("Invalid IPN secret provided.");
            }

        } elseif ($post_data['ipn_mode'] == 'hmac') {

            $hmac = hash_hmac("sha512", file_get_contents('php://input'), $this->ipn_secret);

            if ($hmac !== $server_data['HTTP_HMAC']) {
                throw new CoinPaymentsException("Invalid HMAC provided.");
            }
            if ($post_data['merchant'] !== $this->merchant_id) {
                throw new CoinPaymentsException("Invalid merchant ID provided.");
            }

        } else {
            throw new CoinPaymentsException("Invalid IPN mode provided.");
        }

        $order_status = $post_data['status'];

        // If $order_status is >100 or is 2, then it is complete
        return ($order_status >= 100 || $order_status == 2);
    }

    /**
     * @return bool
     */
    private function isSetup ()
    {
        return (!empty($this->private_key) && !empty($this->public_key));
    }

    /**
     * Gets the current CoinPayments.net merchant profile info.
     *
     * @return array|mixed
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    public function getPBNInfo ()
    {
        return $this->apiCall(CoinpaymentsCommand::GET_PBN_INFO, ['merchant' => $this->merchant_id]);
    }

    public function getBasicInfo ()
    {
        return $this->apiCall(CoinpaymentsCommand::GET_BASIC_INFO);
    }

    /**
     * @param string $cmd the command to be executed
     * @param array  $req
     * @return Receipt
     * @throws CoinPaymentsException
     * @throws JsonParseException
     * @throws MessageSendException
     */
    protected function apiCall ($cmd, $req = [])
    {
        if (!$this->isSetup()) {
            throw new CoinPaymentsException('You have not called the Setup function with your private and public keys!');
        }

        // Set the API command and required fields
        $req['version'] = 1;
        $req['cmd']     = $cmd;
        $req['key']     = $this->public_key;
        $req['format']  = isset($req['format']) && !empty($req['ipn_url']) ? $req['format'] : $this->format; //supported values are json and xml
        $req['ipn_url'] = isset($req['ipn_url']) && !empty($req['ipn_url']) ? $req['ipn_url'] : $this->ipn_url;

        // Generate the query string
        $postData = http_build_query($req, '', '&');

        // Calculate the HMAC signature on the POST data
        $hmac = hash_hmac('sha512', $postData, $this->private_key);

        // Create cURL handle and initialize (if needed)
        if ($this->ch === null) {
            $this->ch = curl_init('https://www.coinpayments.net/api.php');
            curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['HMAC: ' . $hmac]);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);

        if (!($data = curl_exec($this->ch))) {
            throw new MessageSendException('cURL error: ' . curl_error($this->ch));
        }

        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $response = json_decode($data, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $response = json_decode($data, true);
        }

        // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
        if ($response === null || !count($response)) {
            cp_log(
                [
                    'data' => $data,
                ],
                'JSON_ERROR',
                Log::LEVEL_ERROR
            );
            throw new JsonParseException('Unable to parse JSON result (' . json_last_error() . ')');
        }

        return new Receipt($cmd, $req, $response);
    }
}
