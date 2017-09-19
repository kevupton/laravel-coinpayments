<?php

namespace Kevupton\LaravelCoinpayments;

class LaravelCoinpayments {

    private $payments;
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
        $this->payments = new Coinpayments(
            cp_conf('private_key'),
            cp_conf('public_key'),
            cp_conf('merchant_id'),
            cp_conf('ipn_secret'),
            cp_conf('ipn_url'),
            cp_conf('format')
        );
    }


}