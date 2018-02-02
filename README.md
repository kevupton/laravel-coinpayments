# Laravel Coinpayments #
Implementation of most of the CoinPayments functionality. 

### [Coinpayments Website](https://www.coinpayments.net/index.php?ref=a458c004de21a18c71849871781be820)

### [Example API](https://github.com/kevupton/example-laravel-coinpayments)

### Require

```
composer require kevupton/laravel-coinpayments
```

### 1. Install Service Provider

```php
// add directly from the app 
$app->register(\Kevupton\LaravelCoinpayments\Providers\LaravelCoinpaymentsServiceProvider::class);
```

OR

All service providers are registered in the `config/app.php` configuration file.
```php
'providers' => [
    // Other Service Providers

    \Kevupton\LaravelCoinpayments\Providers\LaravelCoinpaymentsServiceProvider::class,
],
```

### 2. Configure

`.env` configuration
```text
COINPAYMENTS_DB_PREFIX=cp_
COINPAYMENTS_MERCHANT_ID=your_unique_merchant_id
COINPAYMENTS_PUBLIC_KEY=generated_public_key
COINPAYMENTS_PRIVATE_KEY=generated_private_key
COINPAYMENTS_IPN_SECRET=your_custom_ipn_secret
COINPAYMENTS_IPN_URL=your_ipn_url
COINPAYMENTS_API_FORMAT=json
```

*Execute `php artisan vendor:publish` for the complete config file.*

Config: `coinpayments.php`
```php

return array(

    // prefix to each of the tables in the database
    'database_prefix' => env('COINPAYMENTS_DB_PREFIX', 'cp_'),

    'merchant_id' => env('COINPAYMENTS_MERCHANT_ID'),

    // Your API public key associated with your coinpayments account
    'public_key' => env('COINPAYMENTS_PUBLIC_KEY'),

    // Your API private key associated with your coinpayments account
    'private_key' => env('COINPAYMENTS_PRIVATE_KEY'),

    // This is used to verify that an IPN is from us, use a good random string nobody can guess.
    'ipn_secret' => env('COINPAYMENTS_IPN_SECRET'),

    // URL for your IPN callbacks. If not set it will use the IPN URL in your Edit Settings page if you have one set.
    'ipn_url' => env('COINPAYMENTS_IPN_URL'),

    // The format of response to return, json or xml. (default: json)
    'format' => env('COINPAYMENTS_API_FORMAT', 'json'),

    // ALL logs all requests, ERROR logs only errors, and NONE never
    'log_level' => Log::LEVEL_ERROR,
);

```

### 3. Setup Database

Run the migration to install the database tables
```shell
php artisan migrate
```

### 4. Usage

Simple transaction
```php

\Coinpayments::createTransactionSimple($cost, $currency_base, $currency_received, $extra_details);

```

IPN validation
```php
try {
    \Coinpayments::validateIPNRequest($request);
    
    // do soemthing with the completed transaction
} catch (\Exception $e) {
    
    // transaction not completed.
}
```

### 4.1 Example Controller

CoinPayments Controller
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kevupton\LaravelCoinpayments\Exceptions\IpnIncompleteException;
use Kevupton\LaravelCoinpayments\Models\Ipn;
use Kevupton\LaravelCoinpayments\Models\Transaction;

class CoinpaymentsController extends Controller
{
    const ITEM_CURRENCY = 'BTC';
    const ITEM_PRICE    = 0.01;

    /**
     * Purchase items using coinpayments payment processor
     *
     * @param Request $request
     * @return array
     */
    public function purchaseItems (Request $request)
    {
        // validate that the request has the appropriate values
        $this->validate($request, [
            'currency' => 'required|string',
            'amount'   => 'required|integer|min:1',
        ]);


        $amount   = $request->get('amount');
        $currency = $request->get('currency');

        /*
         * Calculate the price of the item (qty * ppu)
         */
        $cost = $amount * self::ITEM_PRICE;

        /** @var Transaction $transaction */
        $transaction = \Coinpayments::createTransactionSimple($cost, self::ITEM_CURRENCY, $currency);

        return ['transaction' => $transaction];
    }

    /**
     * Creates a donation transaction
     *
     * @param Request $request
     * @return array
     */
    public function donation (Request $request)
    {
        // validate that the request has the appropriate values
        $this->validate($request, [
            'currency' => 'required|string',
            'amount'   => 'required|integer|min:0.01',
        ]);

        $amount   = $request->get('amount');
        $currency = $request->get('currency');

        /*
         * Here we are donating the exact amount that they specify.
         * So we use the same currency in and out, with the same amount value.
         */
        $transaction = \Coinpayments::createTransactionSimple($amount, $currency, $currency);

        return ['transaction' => $transaction];
    }

    /**
     * Handled on callback of IPN
     *
     * @param Request $request
     */
    public function validateIpn (Request $request)
    {
        try {
            /** @var Ipn $ipn */
            $ipn = \Coinpayments::validateIPNRequest($request);

            // if the ipn came from the API side of coinpayments merchant
            if ($ipn->isApi()) {

                /*
                 * If it makes it into here then the payment is complete.
                 * So do whatever you want once the completed
                 */

                // do something here
                // Payment::find($ipn->txn_id);
            }
        }
        catch (IpnIncompleteException $e) {
            $ipn = $e->getIpn();
            /*
             * Can do something here with the IPN model if desired.
             */
        }
    }
}
```

### 5. Logging

Adjust the logging in the config file by selecting either `LEVEL_NONE`, `LEVEL_ERROR` or `LEVEL_ALL`.

Logs will be saved into the database under `$prefix . 'log'`.

### Contributing

Feel free to make a pull request at any time. Any help is appreciated (Y)
