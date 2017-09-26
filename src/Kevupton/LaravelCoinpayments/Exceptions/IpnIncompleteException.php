<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 12:50 PM
 */

namespace Kevupton\LaravelCoinpayments\Exceptions;


use Kevupton\LaravelCoinpayments\Models\Ipn;
use Throwable;

class IpnIncompleteException extends \Exception
{
    /**
     * @var Ipn
     */
    private $ipn;

    public function __construct ($message = "", Ipn $ipn, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->ipn = $ipn;
    }

    /**
     * @return Ipn
     */
    public function getIpn ()
    {
        return $this->ipn;
    }
}