<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 24/03/2018
 * Time: 3:50 PM
 */

namespace Kevupton\LaravelCoinpayments\Enums;

class IpnType
{
    const DEPOSIT    = 'deposit';
    const WITHDRAWAL = 'withdrawal';
    const API        = 'api';
}