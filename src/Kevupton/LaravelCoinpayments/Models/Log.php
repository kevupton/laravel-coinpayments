<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 12:31 PM
 */

namespace Kevupton\LaravelCoinpayments\Models;

class Log extends Model
{
    protected $table = 'log';

    const LEVEL_ALL = 2;
    const LEVEL_ERROR = 1;
    const LEVEL_NONE = 0;

    public $fillable = [
        'type', 'log'
    ];
}