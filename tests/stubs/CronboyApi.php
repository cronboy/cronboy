<?php

namespace Cronboy\Cronboy\Tests\stubs;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Class NotifierApi.
 */
class CronboyApi
{
    /**
     * @return Client
     */
    public static function clientWithResponse($responses)
    {
        if (!is_array($responses)) {
            $responses = [$responses];
        }

        return new Client([
            'handler' => HandlerStack::create(
                new MockHandler($responses)
            ),
        ]);
    }
}
