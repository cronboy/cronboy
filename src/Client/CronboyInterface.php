<?php

namespace Cronboy\Cronboy\Client;

use Carbon\Carbon;
use Cronboy\Cronboy\Client\Exceptions\InvalidApiTokenException;
use Cronboy\Cronboy\Client\Exceptions\InvalidAppKeyException;

/**
 * Class CronboySaaS.
 */
interface CronboyInterface
{
    /**
     * @param string $url
     * @param string $verb
     * @param array  $params
     * @param Carbon $time_to_execute
     *
     * @throws InvalidApiTokenException
     * @throws InvalidAppKeyException
     *
     * @return CreateEventResponse
     */
    public function createJob($url, $verb, array $params, Carbon $time_to_execute);
}
