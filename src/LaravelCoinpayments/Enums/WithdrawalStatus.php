<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 24/03/2018
 * Time: 8:22 PM
 */

namespace Oasin\LaravelCoinpayments\Enums;

class WithdrawalStatus
{
    const WAITING_EMAIL_CONFIRMATION = 0;
    const PENDING = 1;
    const COMPLETE = 2;
}