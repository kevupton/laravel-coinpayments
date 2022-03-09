<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 2:15 PM
 */

namespace Oasin\LaravelCoinpayments;


use Illuminate\Contracts\Support\Arrayable;

class Receipt implements Arrayable
{

    private $command;
    private $request;
    private $response;

    public function __construct($command, $request, $response)
    {
        $this->command = $command;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function hasError () {
        return $this->response['error'] != 'ok';
    }

    /**
     * @return string
     */
    public function getError () {
        return $this->hasError() ? $this->response['error'] : null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray () {
        return array_merge($this->request, $this->response['result']);
    }
}