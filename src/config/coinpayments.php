<?php

return array(

    // prefix to each of the tables in the database
    'database_prefix' => 'cp_',

    // Your API public key associated with your coinpayments account
    'public_key' => getenv('COINPAYMENTS_PUBLIC_KEY'),

    // The format of response to return, json or xml. (default: json)
    'format' => getenv('COINPAYMENTS_API_FORMAT', 'json'),

    // The API Passphrase from the Direct API from the Merchant Warrior Account.
    'api_passphrase' => getenv('COINPAYMENTS_API_PASSPHRASE'),

    // This is used to verify that an IPN is from us, use a good random string nobody can guess.
    'ipn_secret' => getenv('COINPAYMENTS_IPN_SECRET'),

    // URL for your IPN callbacks. If not set it will use the IPN URL in your Edit Settings page if you have one set.
    'ipn_url' => getenv('COINPAYMENTS_IPN_URL'),

    // Whether or not to save a log of the requests
    'log_requests' => true,
);
