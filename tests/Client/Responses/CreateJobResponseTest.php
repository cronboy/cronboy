<?php

use Cronboy\Cronboy\Client\Responses\CreateJobResponse;

/**
 * Class CreateJobResponseTest.
 */
class CreateJobResponseTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_returns_an_task_id_when_correct_response_is_given()
    {
        $createJobResponse = new CreateJobResponse(
            new \GuzzleHttp\Psr7\Response(201, [], '{"id":33}')
        );

        $this->assertEquals(33, $createJobResponse->id());
    }

    /** @test */
    public function it_throws_an_exception_when_not_success_response_was_received()
    {
        $this->expectException(\Cronboy\Cronboy\Client\Exceptions\InvalidCronboySaaSResponse::class);

        $createJobResponse = new CreateJobResponse(
            new \GuzzleHttp\Psr7\Response(201, [], '{"status":"error"}')
        );

        $createJobResponse->id();
    }
}
