<?php

namespace Kevupton\LaravelCoinpayments\Enums;

abstract class CoinpaymentsCommand
{
    const CONVERT = 'convert';
    const CREATE_MASS_WITHDRAWAL = 'create_mass_withdrawal';
    const CREATE_TRANSACTION = 'create_transaction';
    const CREATE_WITHDRAWAL = 'create_withdrawal';
    const CREATE_TRANSFER = 'create_transfer';
    const GET_TX_INFO = 'get_tx_info';
    const GET_WITHDRAWAL_INFO = 'get_withdrawal_info';
    const GET_CONVERSION_INFO = 'get_conversion_info';
    const GET_CALLBACK_ADDRESS = 'get_callback_address';
    const BALANCES = 'balances';
    const RATES = 'rates';
    const GET_DEPOSIT_ADDRESS = 'get_deposit_address';
    const GET_PBN_INFO = 'get_pbn_info';
    const GET_BASIC_INFO = 'get_basic_info';
}
