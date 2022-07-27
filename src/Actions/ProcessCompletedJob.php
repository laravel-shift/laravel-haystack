<?php

namespace Sammyjo20\LaravelHaystack\Actions;

use Illuminate\Queue\Events\JobProcessed;
use Sammyjo20\LaravelHaystack\Models\Haystack;

class ProcessCompletedJob
{
    /**
     * Constructor
     *
     * @param  JobProcessed  $jobProcessed
     */
    public function __construct(protected JobProcessed $jobProcessed)
    {
        //
    }

    /**
     * Attempt to find the haystack_id on the processed job.
     *
     * @return void
     */
    public function execute(): void
    {
        $job = $this->jobProcessed->job;

        $haystackId = $job->payload()['data']['haystack_id'] ?? null;

        if (blank($haystackId)) {
            return;
        }

        // Now we'll try to find the Haystack from the ID.

        $haystack = Haystack::findOrFail($haystackId);

        // Once we have found the Haystack, we'll check if the current job
        // has failed. If it has, then we'll fail the whole stack. Otherwise,
        // we will dispatch the next job.

        $job->hasFailed() ? $haystack->fail() : $haystack->dispatchNextJob();
    }
}
