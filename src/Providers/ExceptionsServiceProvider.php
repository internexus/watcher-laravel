<?php

namespace Internexus\WatcherLaravel\Providers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\ServiceProvider;

class ExceptionsServiceProvider extends ServiceProvider
{
    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(MessageLogged::class, function (MessageLogged $log) {
            $this->handleLog($log->level, $log->message, $log->context);
        });
    }

    /**
     * Handle application logs.
     *
     * @param  string  $level
     * @param  mixed   $message
     * @param  mixed   $context
     * @return void
     */
    protected function handleLog($level, $message, $context)
    {
        if (
            isset($context['exception']) &&
            ($context['exception'] instanceof \Exception || $context['exception'] instanceof \Throwable)
        ) {
            return $this->reportException($context['exception']);
        }

        if ($message instanceof \Exception || $message instanceof \Throwable) {
            return $this->reportException($message);
        }

        if ($this->app['watcher']->isCapturing()) {
            $logs = $this->app['watcher']->current()->getContext('logs') ?? [];
            $data = array_merge($logs, [compact('level', 'message')]);

            $this->app['watcher']->current()->addContext('logs', $data);
        }
    }

    /**
     * Exception report.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    protected function reportException(\Throwable $exception)
    {
        $this->app['watcher']->reportException($exception);
    }
}
