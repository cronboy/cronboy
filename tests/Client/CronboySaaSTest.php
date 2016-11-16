<?php

use Carbon\Carbon;
use Cronboy\Cronboy\Client\CronboySaaS;
use Cronboy\Cronboy\Client\Exceptions\InvalidApiTokenException;
use Cronboy\Cronboy\Client\Exceptions\InvalidAppKeyException;
use Cronboy\Cronboy\Client\Exceptions\InvalidArgumentsException;
use Cronboy\Cronboy\Exceptions\InvalidScheduleTimeException;
use Cronboy\Cronboy\Tests\stubs\CronboyApi;
use GuzzleHttp\Psr7\Response;

/**
 * Class CronboySaaSTest.
 */
class CronboySaaSTest extends Orchestra\Testbench\TestCase
{
    /** @test */
    public function it_returns_an_task_id_on_create_event_success_call()
    {
        $notifierClient = $this->mockCronboySaaSClientWithTestResponse(
            new Response(201, [], '{"id": 1}')
        );

        $task_id = $notifierClient->createJob('/receive/webhook', 'POST', ['param1' => 'value1'], Carbon::now()->addDay(1))->id();

        $this->assertEquals(1, $task_id);
    }

    /** @test */
    public function it_throws_an_invalid_api_token_exception_when_api_token_is_incorrect()
    {
        $this->expectException(InvalidApiTokenException::class);

        $notifierClient = $this->mockCronboySaaSClientWithTestResponse(
            new Response(401, [], ' {"errors": {"code": 401, "title": "Unauthenticated.", "description": ""}}')
        );

        $notifierClient->createJob('/test/url', 'POST', ['param1' => 'value1'], Carbon::now()->addDay(1));
    }

    /** @test */
    public function it_throws_an_invalid_app_key_exception_when_app_key_is_invalid()
    {
        $this->expectException(InvalidAppKeyException::class);

        $notifierClient = $this->mockCronboySaaSClientWithTestResponse(
            new Response(403, [], '{"errors": {"code": 401,"title": "Invalid application key is provided","description": ""}}')
        );

        $notifierClient->createJob('/test/url', 'POST', ['param1' => 'value1'], Carbon::now()->addDay(1));
    }

    /** @test */
    public function it_throws_an_client_exception_when_notifier_service_responded_with_bad_request_error()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);

        $notifierClient = $this->mockCronboySaaSClientWithTestResponse(
            new Response(400, [], '{"errors":{"code": 400,"title":"Bad request.","description":""}}')
        );

        $notifierClient->createJob('/test/url', 'POST', ['param1' => 'value1'], Carbon::now()->addDay(1));
    }

    /** @test */
    public function it_throws_an_internal_server_error_exception_when_an_error_with_500_is_received_from_notifier_service()
    {
        $this->expectException(\GuzzleHttp\Exception\ServerException::class);

        $notifierClient = $this->mockCronboySaaSClientWithTestResponse(
            new Response(500, [], '{"errors":{"code":500,"title":"Internal server error.","description":""}}')
        );

        $notifierClient->createJob('/test/url', 'POST', ['param1' => 'value1'], Carbon::now()->addDay(1));
    }

    /** @test */
    public function it_throws_a_validation_exception_if_incorrect_data_is_passed_for_create_task_method()
    {
        $this->expectException(InvalidArgumentsException::class);
        (new CronboySaaS($this->getApiToken(), $this->getAppKey()))->createJob('', 'PATCH', [], Carbon::now()->addDay(1));
    }

    /** @test */
//    public function it_throws_a_invalid_schedule_time_exception_when_the_time_before_now_is_passed()
//    {
//        $this->expectException(InvalidScheduleTimeException::class);
//        (new CronboySaaS($this->getApiToken(), $this->getAppKey()))->createJob('', 'POST', [], Carbon::now()->subDay(1));
//    }

    /**
     * @return string
     */
    protected function getAppKey()
    {
        return '113b26a739c368a7ec4f08c8691a23a2c';
    }

    /**
     * @return string
     */
    protected function getApiToken()
    {
        return '$2y$10$WfdTBmwyffupEwsw.KgAXOYoz1nVa.cLJDNF8ACSe4kGj3oZYk7UK';
    }

    /**
     * @param Response $response
     *
     * @return CronboySaaS
     */
    private function mockCronboySaaSClientWithTestResponse(Response $response)
    {
        return new CronboySaaS(
            $this->getApiToken(),
            $this->getAppKey(),
            CronboyApi::clientWithResponse(
                $response
            )
        );
    }
}
