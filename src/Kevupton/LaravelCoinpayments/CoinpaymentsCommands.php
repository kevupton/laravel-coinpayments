<?php

namespace Kevupton\LaravelCoinpayments;

abstract class CoinpaymentsCommands {
    const CREATE_TRANSACTION = 'create_transaction';
    const CREATE_WITHDRAWAL = 'create_withdrawal';
    const CREATE_TRANSFER = 'create_transfer';
    const GET_TX_INFO = 'get_tx_info';
    const GET_CALLBACK_ADDRESS = 'get_callback_address';
    const BALANCES = 'balances';
    const RATES = 'rates';
}