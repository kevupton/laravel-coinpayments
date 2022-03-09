<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 12:31 PM
 */

namespace Oasin\LaravelCoinpayments\Models;

/**
 * Class Transaction
 *
 * @package Oasin\LaravelCoinpayments\Models
 * @property mixed id
 * @property mixed amount
 * @property mixed from
 * @property mixed to
 * @property mixed address
 * @property mixed dest_tag
 * @property mixed created_at
 * @property mixed updated_at
 */
class Conversion extends Model
{
    protected $fillable = [
        'amount', 'from', 'to', 'address', 'dest_tag', 'ref_id'
    ];
}