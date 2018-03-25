<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 24/03/2018
 * Time: 6:42 PM
 */

namespace Kevupton\LaravelCoinpayments\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Kevupton\LaravelCoinpayments\Exceptions\CoinPaymentsException;
use Kevupton\LaravelCoinpayments\LaravelCoinpayments;

class CoinpaymentsController extends Controller
{
    public function validateIPN (Request $request)
    {
        /** @var LaravelCoinpayments $coinpayments */
        $coinpayments = app('coinpayments');

        try {
            $coinpayments->validateIPNRequest($request);
        } catch (CoinPaymentsException $e) {
            return response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}