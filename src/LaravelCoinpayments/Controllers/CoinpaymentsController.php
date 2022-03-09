<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 24/03/2018
 * Time: 6:42 PM
 */

namespace Oasin\LaravelCoinpayments\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Oasin\LaravelCoinpayments\Exceptions\IpnIncompleteException;
use Oasin\LaravelCoinpayments\LaravelCoinpayments;
use Oasin\LaravelCoinpayments\Models\Log;

class CoinpaymentsController extends Controller
{
    public function validateIPN (Request $request)
    {
        /** @var LaravelCoinpayments $coinpayments */
        $coinpayments = app('coinpayments');

        try {
            $coinpayments->validateIPNRequest($request);
        } catch (IpnIncompleteException $e) {
            cp_log($e->getIpn()->toArray(), 'IPN_INCOMPLETE', Log::LEVEL_ALL);
            return response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        
        return response(Response::HTTP_OK);
    }
}
