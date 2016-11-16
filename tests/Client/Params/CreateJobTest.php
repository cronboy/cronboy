<?php

use Carbon\Carbon;
use Cronboy\Cronboy\Client\Params\CreateJob;

/**
 * Class CreateEventTest.
 */
class CreateJobTest extends Orchestra\Testbench\TestCase
{
    /**
     * @var string
     */
    protected $json;

    /**
     * @var
     */
    protected $params;

    protected function setUp()
    {
        parent::setUp();

        $timeToExecute = Carbon::now()->addDay(1);

        $this->json = '{"url":"\/test","verb":"POST","params":{"param1":"test"},"time_to_execute":"'.$timeToExecute->toDateTimeString().'"}';

        $this->params = [
            'url'             => '/test',
            'verb'            => 'POST',
            'time_to_execute' => $timeToExecute,
            'params'          => [
                'param1' => 'test',
            ],
        ];
    }

    /** @test */
    public function it_returns_params_in_defined_json_format()
    {
        $createJobParams = new CreateJob(
            $this->params['url'], $this->params['verb'], $this->params['time_to_execute'], $this->params['params']
        );

        $this->assertEquals($this->json, $createJobParams->toJson());
    }

    /** @test */
    public function it_throw_an_invalid_params_arguments_exception_when_incorrect_params_a_given()
    {
        $this->expectException(\Cronboy\Cronboy\Client\Exceptions\InvalidArgumentsException::class);

        new CreateJob(
            null, $this->params['verb'], $this->params['time_to_execute'], $this->params['params']
        );
    }

    /** @test */
    public function it_converts_time_to_execute_param_to_utc_timezone()
    {
        $this->params['time_to_execute'] = Carbon::now('America/Denver')->addDay(1);

        $createJobParams = new CreateJob(
            $this->params['url'], $this->params['verb'], $this->params['time_to_execute'], $this->params['params']
        );

        $this->assertEquals($this->params['time_to_execute']->tz('UTC'), $createJobParams->toArray()['time_to_execute']);
    }
}
