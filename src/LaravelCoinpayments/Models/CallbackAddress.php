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
 * @property number         id
 * @property string         address
 * @property string         currency
 * @property string         pubkey
 * @property string         dest_tag
 * @property string         ipn_url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CallbackAddress extends Model
{
    public $fillable = [
        'address', 'currency', 'pubkey', 'dest_tag', 'ipn_url'
    ];
}