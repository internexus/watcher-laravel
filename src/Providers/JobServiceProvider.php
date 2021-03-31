<?php

namespace Internexus\WatcherLaravel\Providers;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Queue\Job;

class JobServiceProvider extends ServiceProvider
{
    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->handleJobStart($event->job);
        });

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event) {
            $this->handleJobEnd($event->job);
        });

        $this->app['events']->listen(JobFailed::class, function (JobFailed $event) {
            $this->handleJobEnd($event->job, true);
        });

        $this->app['events']->listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event) {
            $this->handleJobEnd($event->job, true);
        });
    }

    /**
     * Determine if the given Job class should be monitored.
     *
     * @param  JobProcessing  $event
     * @return boolean
     */
    protected function shouldMonitor($event)
    {
        $notAllowed = config('watcher.ignore_jobs');

        return is_array($notAllowed) ? !in_array($event->job->resolveName(), $notAllowed) : true;
    }

    /**
     * Handle with started job.
     *
     * @param  Job  $job
     * @return void
     */
    protected function handleJobStart(Job $job)
    {
        if (! $this->shouldMonitor($job)) {
            return;
        }

        if ($this->app['watcher']->isCapturing()) {
            $this->initializeSegment($job);
        } else {
            $this->app['watcher']->transaction($job->resolveName())->addContext('payload', $job->payload());
        }
    }

    /**
     * Handle with ended job.
     *
     * @param  Job      $job
     * @param  boolean  $failed
     * @return void
     */
    protected function handleJobEnd(Job $job, $failed = false)
    {
        if (! $this->app['watcher']->isCapturing()) {
            return;
        }

        $id = $this->getJobId($job);

        // If a segment doesn't exists it means that job is registered as transaction
        // we can set the result accordingly
        if (array_key_exists($id, $this->segments)) {
            $this->segments[$id]->end();
        } else {
            $this->app['watcher']->current()->setResult($failed ? 'error' : 'success');
        }

        if ($this->app->runningInConsole()) {
            $this->app['watcher']->flush();
        }
    }

    /**
     * Start segment on current transaction.
     *
     * @param  Job  $job
     * @return void
     */
    protected function initializeSegment(Job $job)
    {
        $this->segments[
            $this->getJobId($job)
        ] = $this->app['watcher']->segment('job', $job->resolveName())->addContext('payload', $job->payload());
    }

     /**
     * Get Job ID.
     *
     * @param  Job  $job
     * @return string|int
     */
    public static function getJobId(Job $job)
    {
        if ($jobId = $job->getJobId()) {
            return $jobId;
        }

        return sha1($job->getRawBody());
    }
}
