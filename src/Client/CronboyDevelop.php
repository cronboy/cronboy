<?php
/**
 * Created by PhpStorm.
 * User: vitsw
 * Date: 11/12/16
 * Time: 2:11 AM
 */

namespace Cronboy\Cronboy\Client;


use Carbon\Carbon;
use Cronboy\Cronboy\Client\CreateEventResponse;
use Cronboy\Cronboy\Client\Exceptions\InvalidApiTokenException;
use Cronboy\Cronboy\Client\Exceptions\InvalidAppKeyException;
use Cronboy\Cronboy\Client\Params;
use Illuminate\Http\Request;
use Cronboy\Cronboy\Services\ParamsSign;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class CronboyDevelop
 * @package Iprojectss\Cron\Client
 */
class CronboyDevelop implements CronboyInterface
{

    /**
     * @param string $url
     * @param string $verb
     * @param array $params
     * @param Carbon $time_to_execute
     *
     * @throws InvalidApiTokenException
     * @throws InvalidAppKeyException
     *
     * @return CreateEventResponse
     */
    public function createJob($url, $verb, array $params, Carbon $time_to_execute)
    {
        $createJobParams = (new Params\CreateJob($url, $verb, $time_to_execute, $params))->toArray();

        $params = $createJobParams['params'];

        $params['signature'] = ParamsSign::make(
            $params, config('cronboy.app_secret')
        );

        $this->handleDevelopCall($url, $verb, $params, clone request());

        return str_random(8);
    }

    /**
     * @param $url
     * @param $verb
     * @param array $params
     * @param $originalRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function handleDevelopCall($url, $verb, array $params, $originalRequest)
    {
        $response = app()->handle(
            Request::createFromBase(
                SymfonyRequest::create($url, $verb, $params)
            )
        );

        app()->instance('request', $originalRequest);

        if (!$response->isSuccessful()) {
            throw $response->exception;
        }

        return $response;
    }
}