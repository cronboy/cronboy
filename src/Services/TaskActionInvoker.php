<?php

namespace Cronboy\Cronboy\Services;

use Closure;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class TaskActionInvoker.
 */
class TaskActionInvoker
{
    use DispatchesJobs;

    /**
     * @param \Closure|\Illuminate\Contracts\Queue\Job
     *
     * @return mixed
     */
    public function run($taskAction)
    {
        if ((is_object($taskAction)) && ($taskAction instanceof Closure)) {
            return $taskAction->__invoke();
        }

        $this->dispatch($taskAction);
    }
}
