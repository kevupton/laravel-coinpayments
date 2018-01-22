# Laravel Coinpayments #
Implementation of most of the CoinPayments functionality. 

### [Coinpayments Website](https://www.coinpayments.net/index.php?ref=a458c004de21a18c71849871781be820)

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

### 5. Logging

Adjust the logging in the config file by selecting either `LEVEL_NONE`, `LEVEL_ERROR` or `LEVEL_ALL`.

Logs will be saved into the database under `$prefix . 'log'`.

### Contributing

Feel free to make a pull request at any time. Any help is appreciated (Y)
