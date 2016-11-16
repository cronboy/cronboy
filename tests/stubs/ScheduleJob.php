<?php

namespace Cronboy\Cronboy\Tests\stubs;

use Illuminate\Contracts\Queue\Job;

/**
 * Class ScheduleJob.
 */
class ScheduleJob implements Job
{
    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        // TODO: Implement fire() method.
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        // TODO: Implement isDeleted() method.
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        // TODO: Implement release() method.
    }

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        // TODO: Implement isDeletedOrReleased() method.
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        // TODO: Implement attempts() method.
    }

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Get the resolved name of the queued job class.
     *
     * @return string
     */
    public function resolveName()
    {
        // TODO: Implement resolveName() method.
    }

    /**
     * Call the failed method on the job instance.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    public function failed($e)
    {
        // TODO: Implement failed() method.
    }

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        // TODO: Implement getQueue() method.
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        // TODO: Implement getRawBody() method.
    }
}
