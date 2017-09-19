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
 * @property mixed currency2
 * @property mixed address
 * @property mixed pbntag
 * @property mixed dest_tag
 * @property mixed ipn_url
 * @property mixed auto_confirm
 * @property mixed note
 * @property mixed ref_id
 * @property mixed status
 * @property mixed created_at
 * @property mixed updated_at
 */
class Withdrawal extends Model
{
    public $fillable = [
        'amount', 'currency', 'currency2', 'address',
        'pbntag', 'dest_tag', 'ipn_url', 'auto_confirm',
        'note', 'ref_id', 'status'
    ];
}