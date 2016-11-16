<?php

namespace Cronboy\Cronboy\Client\Responses;

use Cronboy\Cronboy\Client\Exceptions\InvalidCronboySaaSResponse;
use GuzzleHttp\Psr7\Response;

/**
 * Class CreateEventResponse.
 */
class CreateJobResponse
{
    /**
     * @var mixed
     */
    protected $response;

    /**
     * CreateEventResponse constructor.
     *
     * @param Response $response
     *
     * @throws InvalidCronboySaaSResponse
     */
    public function __construct(Response $response)
    {
        $this->response = \GuzzleHttp\json_decode(
            $response->getBody(), true
        );

        if (!array_key_exists('id', $this->response)) {
            throw new InvalidCronboySaaSResponse(\GuzzleHttp\json_encode($this->response));
        }
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->response['id'];
    }
}
