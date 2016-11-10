<?php
/**
 * Created by PhpStorm.
 * User: vitsw
 * Date: 10/12/16
 * Time: 2:18 AM
 */

namespace Cronboy\Cronboy;


use Carbon\Carbon;
use DateTimeZone;
use Cronboy\Cronboy\Exceptions\InvalidArgumentException;
use Cronboy\Cronboy\Exceptions\InvalidScheduleTimeException;
use Cronboy\Cronboy\Client\CronboySaaS;
use Cronboy\Cronboy\Services\RequestRunner;
use Cronboy\Cronboy\Services\SerializerService;

/**
 * Class Cronboy
 * @package Cronboy\Cronboy
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
     * @var CronboySaaS
     */
    private $cronboySaaS;

    /**
     * @var SerializerService
     */
    private $serializer;

    /**
     * Scheduler constructor.
     * @param CronboySaaS $cronboySaaS
     * @param SerializerService $serializer
     */
    public function __construct(CronboySaaS $cronboySaaS, SerializerService $serializer)
    {
        $this->cronboySaaS = $cronboySaaS;
        $this->serializer = $serializer;
        $this->resetVerb();
    }

    /**
     * @param Carbon|string $timeToExecute
     * @param string|DateTimeZone $timezone
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
     * @return Cronboy
     */
    public function afterOneMinute($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinute());
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function afterFiveMinutes($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinutes(5));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function afterTenMinutes($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinutes(10));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function afterHalfAnHour($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMinutes(30));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function afterAnHour($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addHours(1));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function afterThreeHour($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addHours(3));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function aWeekLater($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addWeek(1));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function afterTwoWeeks($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addWeek(2));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function aMonthLater($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMonth(1));
    }

    /**
     * @param string|DateTimeZone $timezone
     * @return Cronboy
     */
    public function inThreeMonths($timezone = null)
    {
        return $this->at(Carbon::now($timezone)->addMonth(3));
    }

    /**
     * @param string $verb
     * @return $this
     */
    public function via($verb = 'POST')
    {
        $this->verb = $verb;
        return $this;
    }

    /**
     * Initialize a verb for call method with default value
     */
    protected function resetVerb()
    {
        $this->verb = 'POST';
    }

    /**
     * @param $url
     * @param array $params
     * @param null $time
     * @return mixed
     * @throws InvalidScheduleTimeException
     */
    public function call($url, array $params, $time = null)
    {
        if (!is_null($time)){
            $this->at($time);
        }

        if (is_null($this->scheduleTime)){
            throw new InvalidScheduleTimeException("You must set schedule time before you are attempting to schedule a task with some of this methods: at, afterOneMinute...");
        }

        # Create an schedule event in service
        $taskId = $this->cronboySaaS->createJob($url, $this->verb, $params, $this->scheduleTime);

        # Reset schedule time for another call
        $this->scheduleTime = null;
        # Reset verb for another call
        $this->resetVerb();

        return $taskId;
    }

    /**
     * @param $task
     * @param null $time
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function dispatch($task, $time = null)
    {
        if ($task instanceof \Closure) {
            $params[RequestRunner::CLOSURE_PARAM_KEY] = $this->serializer->serializeClosure($task);
        } elseif (is_object($task)){
            $params[RequestRunner::JOB_PARAM_KEY] = $this->serializer->serializeJob($task);
        } else {
            throw new InvalidArgumentException('Task must be a closure or an object');
        }

        return $this->via('POST')->call('/cronboy/task/handle', $params, $time);
    }
}