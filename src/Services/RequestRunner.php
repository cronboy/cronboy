<?php

namespace Cronboy\Cronboy\Services;

use Illuminate\Http\Request;

/**
 * Class RequestRunner.
 */
class RequestRunner
{
    const CLOSURE_PARAM_KEY = 'closure';
    const JOB_PARAM_KEY = 'job';

    /**
     * @var SerializerService
     */
    protected $serializer;

    /**
     * @var TaskActionInvoker
     */
    protected $invoker;

    /**
     * RequestRunner constructor.
     */
    public function __construct()
    {
        $this->serializer = new SerializerService();
        $this->invoker = new TaskActionInvoker();
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function run($request)
    {
        if ($this->requestContainsClosure($request)) {
            $taskAction = $this->serializer->unserializeClosure($request->get(self::CLOSURE_PARAM_KEY));
        } else {
            $taskAction = $this->serializer->unserializeJob($request->get(self::JOB_PARAM_KEY));
        }

        return $this->invoker->run($taskAction);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function requestContainsClosure($request)
    {
        return !is_null($request->get(self::CLOSURE_PARAM_KEY));
    }

    /**
     * @param SerializerService $serializer
     *
     * @return $this
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param TaskActionInvoker $invoker
     *
     * @return $this
     */
    public function setInvoker($invoker)
    {
        $this->invoker = $invoker;

        return $this;
    }
}
