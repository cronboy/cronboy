<?php
/**
 * Created by PhpStorm.
 * User: vitsw
 * Date: 9/29/16
 * Time: 2:13 AM
 */

namespace Cronboy\Cronboy\Client;


use Carbon\Carbon;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Cronboy\Cronboy\Exceptions\InvalidArgumentException;
use Cronboy\Cronboy\Exceptions\InvalidScheduleTimeException;
use Cronboy\Cronboy\Client\Exceptions\InvalidApiTokenException;
use Cronboy\Cronboy\Client\Exceptions\InvalidAppKeyException;
use Cronboy\Cronboy\Client\Exceptions\InvalidArgumentsException;
use Cronboy\Cronboy\Client\Params\CreateJob;
use Cronboy\Cronboy\Client\Responses\CreateJobResponse;

/**
 * Class CronboySaaS
 * @package Cronboy\Cronboy\Client
 */
class CronboySaaS
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var
     */
    private $api_token;

    /**
     * @var
     */
    private $app_key;

    /**
     * NotifierService constructor.
     * @param $api_token
     * @param $app_key
     * @param HttpClient $httpClient
     */
    public function __construct($api_token, $app_key, HttpClient $httpClient = null)
    {
        $this->api_token = $api_token;
        $this->app_key = $app_key;

        $this->httpClient = $httpClient ?? new HttpClient([
                'base_uri' => $this->getUrl(),
            ]);
    }

    /**
     * @param string $url
     * @param string $verb
     * @param array $params
     * @param Carbon $time_to_execute
     * @return CreateEventResponse
     * @throws InvalidApiTokenException
     * @throws InvalidAppKeyException
     */
    public function createJob($url, $verb, array $params, Carbon $time_to_execute)
    {
        try {
            return $this->makeCreateJobApiCall(
                new CreateJob($url, $verb, $time_to_execute, $params)
            );
        } catch (InvalidArgumentsException $e) {
            $this->throwArgumentsExceptionsIfTheCase($e);
        } catch (ClientException $e) {
            # Get errors from response body
            $errors = \GuzzleHttp\json_decode($e->getResponse()->getBody(), true);

            # Throw a specific exception depends on the response
            $this->throwInvalidApiTokenExceptionIfIsTheCase($e, $errors);
            $this->throwInvalidAppKeyExceptionIfIsTheCase($e, $errors);

            # Rethrow Guzzle Exception if not specified error response was received
            throw $e;
        }
    }

    /**
     * @param CreateJob $createJobParams
     * @return CreateEventResponse
     */
    private function makeCreateJobApiCall(CreateJob $createJobParams)
    {
        $options = [
            'query' => [
                'api_token' => $this->getApiToken()
            ],
            'json' => $createJobParams->toArray()
        ];

        return new CreateJobResponse(
            $this->httpClient->post("api/v1/tasks/" . $this->getAppKey(), $options)
        );
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        return 'http://cron_saas_client.dev';
    }

    /**
     * @return mixed
     */
    private function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * @return string
     */
    private function getAppKey()
    {
        return $this->app_key;
    }

    /**
     * @param $e
     * @param $errors
     * @throws InvalidApiTokenException
     */
    private function throwInvalidApiTokenExceptionIfIsTheCase($e, $errors)
    {
        if ($e->getCode() == 401 && $errors['errors']['title'] == 'Unauthenticated.') {
            throw new InvalidApiTokenException("Invalid <api token> is provided for Cronboy Service");
        }
    }

    /**
     * @param $e
     * @param $errors
     * @throws InvalidAppKeyException
     */
    private function throwInvalidAppKeyExceptionIfIsTheCase($e, $errors)
    {
        if ($e->getCode() == 403 && $errors['errors']['title'] == 'Invalid application key is provided') {
            throw new InvalidAppKeyException("Invalid <application key> is provided for Cronboy Service");
        }
    }

    /**
     * @param InvalidArgumentsException $e
     * @throws InvalidArgumentException
     * @throws InvalidScheduleTimeException
     */
    private function throwArgumentsExceptionsIfTheCase(InvalidArgumentsException $e)
    {
        $errors = $e->getErrors();

        if ($errors->has('time_to_execute')) {
            throw new InvalidScheduleTimeException($errors->first('time_to_execute'));
        }

        if ($errors->count()) {
            throw new InvalidArgumentException("Incorrect arguments are given for Cronboy: {$errors->first()}");
        }
    }
}