<?php
/**
 * Created by PhpStorm.
 * User: vitsw
 * Date: 10/12/16
 * Time: 2:20 AM.
 */
use Carbon\Carbon;
use Cronboy\Cronboy\Cronboy;
use Cronboy\Cronboy\Exceptions\InvalidScheduleTimeException;
use Cronboy\Cronboy\Services\RequestRunner;
use Mockery as m;

/**
 * Class SchedulerTest.
 */
class CronboyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cronboy
     */
    protected $cronboy;

    /**
     * @var m\MockInterface
     */
    protected $cronboySaaSClient;

    /**
     * @var m\MockInterface
     */
    protected $serializer;

    /**
     * Create scheduler and notifierClient spy before each test.
     */
    protected function setUp()
    {
        // Time must be relative to test start
        Carbon::setTestNow(Carbon::now());

        $this->cronboySaaSClient = m::spy(\Cronboy\Cronboy\Client\CronboySaaS::class);
        $this->serializer = m::mock(\Cronboy\Cronboy\Services\SerializerService::class);
        $this->cronboy = new Cronboy($this->cronboySaaSClient, $this->serializer);
    }

    /**
     * Clear resources after each test.
     */
    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function it_is_instantiable()
    {
        $this->assertInstanceOf(Cronboy::class, $this->cronboy);
    }

    /** @test */
    public function it_can_schedule_a_webhook_after_one_minute()
    {
        $scheduleWebhook = $this->scheduleParameters();

        $this->cronboy
            ->afterOneMinute()
            ->call($scheduleWebhook['url'], $scheduleWebhook['params']);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with($scheduleWebhook['url'], 'POST', $scheduleWebhook['params'], m::on(function ($time_to_execute) {
                return $time_to_execute->eq(Carbon::now()->addMinute());
            }));
    }

    /** @test */
    public function it_can_schedule_a_webhook_after_five_minutes()
    {
        $scheduleWebhook = $this->scheduleParameters();

        $this->cronboy
            ->afterFiveMinutes()
            ->call($scheduleWebhook['url'], $scheduleWebhook['params']);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with($scheduleWebhook['url'], 'POST', $scheduleWebhook['params'], m::on(function ($time_to_execute) {
                return $time_to_execute->eq(Carbon::now()->addMinute(5));
            }));
    }

    /** @test */
    public function it_can_schedule_a_webhook_at_custom_time()
    {
        $scheduleWebhook = $this->scheduleParameters();

        $this->cronboy
            ->at(Carbon::now()->addHours(5))
            ->call($scheduleWebhook['url'], $scheduleWebhook['params']);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with($scheduleWebhook['url'], 'POST', $scheduleWebhook['params'], m::on(function ($time_to_execute) {
                return $time_to_execute->eq(Carbon::now()->addHours(5));
            }));
    }

    /** @test */
    public function it_can_receive_schedule_time_as_a_string()
    {
        $scheduleWebhook = $this->scheduleParameters();

        $this->cronboy
            ->at('+ 5 hours')
            ->call($scheduleWebhook['url'], $scheduleWebhook['params']);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with($scheduleWebhook['url'], 'POST', $scheduleWebhook['params'], m::on(function ($time_to_execute) {
                return $time_to_execute->eq(Carbon::now()->addHour(5));
            }));
    }

    /** @test */
    public function it_can_get_schedule_time_from_second_argument()
    {
        $scheduleWebhook = $this->scheduleParameters();

        $this->cronboy
            ->call($scheduleWebhook['url'], $scheduleWebhook['params'], Carbon::now()->addHours(5));

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with($scheduleWebhook['url'], 'POST', $scheduleWebhook['params'], m::on(function ($time_to_execute) {
                return $time_to_execute->eq(Carbon::now()->addHours(5));
            }));
    }

    /** @test */
    public function it_throws_an_schedule_time_not_set_exception_when_schedule_time_is_not_set()
    {
        $this->expectException(InvalidScheduleTimeException::class);

        $scheduleWebhook = $this->scheduleParameters();

        $this->cronboy
            ->call($scheduleWebhook['url'], $scheduleWebhook['params']);
    }

    /** @test */
    public function it_can_schedule_a_closure()
    {
        $closure = function () {
            echo 1 + 10;
        };

        $this->serializer
            ->shouldReceive('serializeClosure')
            ->with($closure)
            ->once()
            ->andReturn('serialized closure');

        $this->cronboy
            ->afterFiveMinutes()
            ->dispatch($closure);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with('/cronboy/task/handle', 'POST', [RequestRunner::CLOSURE_PARAM_KEY => 'serialized closure'], m::on(function ($time_to_execute) {
                return $time_to_execute->eq(Carbon::now()->addMinutes(5));
            }));
    }

    /** @test */
    public function it_can_schedule_a_job()
    {
        $job = new \Cronboy\Cronboy\Tests\stubs\ScheduleJob();

        $this->serializer
            ->shouldReceive('serializeJob')
            ->with($job)
            ->once()
            ->andReturn('serialized job');

        $this->cronboy
            ->afterFiveMinutes()
            ->dispatch($job);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with(
                '/cronboy/task/handle', 'POST', [RequestRunner::JOB_PARAM_KEY => 'serialized job'],
                m::on(function ($time_to_execute) {
                    return $time_to_execute->eq(Carbon::now()->addMinutes(5));
                })
            );
    }

    /** @test */
    public function it_throw_an_invalid_argument_exception_when_task_parameter_is_not_a_closure_or_a_valid_task_object()
    {
        $this->expectException(\Cronboy\Cronboy\Exceptions\InvalidArgumentException::class);

        $this->cronboy->afterFiveMinutes()->dispatch('Invalid parameter');
    }

    /** @test */
    public function it_can_receive_a_time_to_execute_in_specified_timezone()
    {
        $this->cronboy
            ->afterFiveMinutes('America/Denver')
            ->call('/webhook-url', []);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with(
                '/webhook-url', 'POST', [],
                m::on(function ($time_to_execute) {
                    return $time_to_execute->eq(Carbon::now('America/Denver')->addMinute(5));
                })
            );
    }

    /** @test */
    public function it_can_change_http_verb_for_plain_webhook()
    {
        $this->cronboy->aMonthLater()
            ->via('GET')
            ->call('/webhook-url', []);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with(
                '/webhook-url', 'GET', [], m::type(Carbon::class)
            );
    }

    /** @test */
    public function it_should_reset_a_verb_action_for_default_value_after_a_call()
    {
        $this->cronboy->aMonthLater()
            ->via('GET')
            ->call('/webhook-url', []);

        $this->cronboy->aMonthLater()
            ->call('/webhook-url', []);

        $this->cronboySaaSClient->shouldHaveReceived('createJob')
            ->with(
                '/webhook-url', 'GET', [], m::type(Carbon::class)
            )->once();
    }

    /**
     * @param Carbon|string $time_to_execute
     *
     * @return array
     */
    private function scheduleParameters($time_to_execute = null)
    {
        return [
            'url'             => '/notify_me_after_one_minute',
            'time_to_execute' => $time_to_execute,
            'params'          => [
                'message' => 'Hello i am a webhook after one minute',
            ],
        ];
    }
}
