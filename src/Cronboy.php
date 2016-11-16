<?php

namespace Cronboy\Cronboy;

use Carbon\Carbon;
use Cronboy\Cronboy\Client\CronboyDevelop;
use Cronboy\Cronboy\Client\CronboyInterface;
use Cronboy\Cronboy\Client\CronboySaaS;
use Cronboy\Cronboy\Client\Exceptions\InvalidArgumentsException;
use Cronboy\Cronboy\Exceptions\InvalidArgumentException;
use Cronboy\Cronboy\Exceptions\InvalidScheduleTimeException;
use Cronboy\Cronboy\Services\RequestRunner;
use Cronboy\Cronboy\Services\SerializerService;
use DateTimeZone;

/**
 * Class Cronboy.
 */
class Cronboy
{
    /**
     * @var Carbon
     */
    protected $scheduleTime;

    /**
     * @var string
     */
    protected $verb;

    /**
     * @var CronboyClient
     */
    private $cronboyClient;

    /**
     * @var SerializerService
     */
    private $serializer;

    /**
     * Scheduler constructor.
     *
     * @param CronboyInterface  $cronboyClient
     * @param SerializerService $serializer
     */
    public function __construct(CronboyInterface $cronboyClient, SerializerService $serializer)
    {
        $this->cronboyClient = $cronboyClient;
        $this->serializer = $serializer;
        $this->reset();
    }

    /**
     * @param Carbon|string       $timeToExecute
     * @param string|DateTimeZone $timezone
     *
     * @return $this
     */
    public function at($timeToExecute, $timezone = null)
    {
        if (is_string($timeToExecute)) {
            $timeToExecute = Carbon::parse($timeToExecute, $timezone);
        }

        $this->scheduleTime = $timeToExecute;

        return $this;
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterOneMinute($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinute());
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterFiveMinutes($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinutes(5));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterTenMinutes($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinutes(10));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterHalfAnHour($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinutes(30));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterAnHour($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addHours(1));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterThreeHour($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addHours(3));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function aWeekLater($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addWeek(1));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function afterTwoWeeks($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addWeek(2));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function aMonthLater($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMonth(1));
    }

    /**
     * @param string|DateTimeZone $timezone
     *
     * @return Cronboy
     */
    public function inThreeMonths($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMonth(3));
    }

    /**
     * @param string $verb
     *
     * @return $this
     */
    public function via($verb = 'POST')
    {
        $this->verb = $verb;

        return $this;
    }

    /**
     * @param $url
     * @param array $params
     * @param null  $time
     *
     * @throws InvalidScheduleTimeException
     *
     * @return mixed
     */
    public function call($url, array $params, $time = null)
    {
        if (!is_null($time)) {
            $this->at($time);
        }

        if (is_null($this->scheduleTime)) {
            throw new InvalidScheduleTimeException('You must set schedule time before you are attempting to schedule a task with some of this methods: at, afterOneMinute...');
        }

        try {
            $taskId = $this->cronboyClient->createJob($url, $this->verb, $params, $this->scheduleTime);
        } catch (InvalidArgumentsException $e) {
            $this->handleInvalidArgumentsException($e);
        }

        // Reset state for next call
        $this->reset();

        return $taskId;
    }

    /**
     * @param $task
     * @param null $time
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function dispatch($task, $time = null)
    {
        if ($task instanceof \Closure) {
            $params[RequestRunner::CLOSURE_PARAM_KEY] = $this->serializer->serializeClosure($task);
        } elseif (is_object($task)) {
            $params[RequestRunner::JOB_PARAM_KEY] = $this->serializer->serializeJob($task);
        } else {
            throw new InvalidArgumentException('Task must be a closure or an object');
        }

        return $this->via('POST')->call('/cronboy/task/handle', $params, $time);
    }

    /**
     * @return Cronboy
     */
    public function debug()
    {
        $this->cronboyClient = app(CronboyDevelop::class);

        return $this;
    }

    /**
     * Initialize state for next call method invoke.
     */
    protected function reset()
    {
        // Reset schedule time for another call
        $this->scheduleTime = null;
        // Reset verb to default value
        $this->verb = 'POST';

        // Reset cronboyClient
        if ($this->cronboyClient instanceof CronboyDevelop) {
            $this->cronboyClient = app(CronboySaaS::class);
        }
    }

    /**
     * @param InvalidArgumentsException $e
     *
     * @throws InvalidArgumentException
     * @throws InvalidScheduleTimeException
     */
    private function handleInvalidArgumentsException(InvalidArgumentsException $e)
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
