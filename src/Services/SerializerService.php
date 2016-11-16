<?php

namespace Cronboy\Cronboy\Services;

use Cronboy\Cronboy\Exceptions\ClosureUnserializationException;
use Illuminate\Contracts\Queue\Job;
use SuperClosure\Exception\ClosureUnserializationException as SuperClosureUnserializationException;
use SuperClosure\Serializer;

/**
 * Class SerializerService.
 */
class SerializerService
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * SerializerService constructor.
     *
     * @param string $signature
     */
    public function __construct($signature = null)
    {
        $this->serializer = new Serializer(null, $signature);
    }

    /**
     * @param \Closure $closure
     *
     * @return string
     */
    public function serializeClosure(\Closure $closure)
    {
        return $this->serializer->serialize($closure);
    }

    /**
     * @param Job $job
     *
     * @return string
     */
    public function serializeJob($job)
    {
        return serialize(clone $job);
    }

    /**
     * @param string $serializedClosure
     *
     * @throws ClosureUnserializationException
     *
     * @return mixed
     */
    public function unserializeClosure($serializedClosure)
    {
        try {
            return $this->serializer->unserialize($serializedClosure);
        } catch (SuperClosureUnserializationException $exception) {
            throw new ClosureUnserializationException();
        }
    }

    /**
     * @param string $serializedJob
     *
     * @throws ClosureUnserializationException
     *
     * @return Job
     */
    public function unserializeJob($serializedJob)
    {
        $unserializedJob = @unserialize($serializedJob);

        if (is_bool($unserializedJob) && !$unserializedJob) {
            throw new ClosureUnserializationException();
        }

        return $unserializedJob;
    }
}
