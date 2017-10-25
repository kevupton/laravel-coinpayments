<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 12:31 PM
 */

namespace Kevupton\LaravelCoinpayments\Models;

/**
 * Class Transaction
 * @package Kevupton\LaravelCoinpayments\Models
 * @property mixed id
 * @property mixed amount
 * @property mixed currency
 * @property mixed merchant
 * @property mixed pbntag
 * @property mixed auto_confirm
 * @property mixed status
 * @property mixed created_at
 * @property mixed updated_at
 */
class Transfer extends Model
{
    public $fillable = [
        'amount', 'currency', 'merchant', 'pbntag',
        'auto_confirm', 'ref_id', 'status'
    ];
}