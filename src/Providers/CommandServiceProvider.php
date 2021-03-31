<?php

namespace Internexus\WatcherLaravel\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->shouldMonitor()) {
            return;
        }

        if (! $this->app['watcher']->isCapturing()) {
            $this->app['watcher']->transaction(implode(' ', $_SERVER['argv']));
        }

        $this->app['events']->listen(CommandFinished::class, function (CommandFinished $event) {
            if($this->app['watcher']->isCapturing()) {
                $this->handleCommandFinished($event);
            }
        });
    }

    /**
     * Determine if the given Job class should be monitored.
     *
     * @return boolean
     */
    protected function shouldMonitor()
    {
        $notAllowed = config('watcher.ignore_commands');
        $input = new ArgvInput();

        return is_null($notAllowed)
            ? true
            : !in_array($input->getFirstArgument(), $notAllowed);
    }

    /**
     * Handle command finished event.
     *
     * @param  \Illuminate\Console\Events\CommandFinished  $event
     * @return void
     */
    protected function handleCommandFinished(CommandFinished $event)
    {
        $this->app['watcher']->current()->addContext('command', [
            'exit_code' => $event->exitCode,
            'arguments' => $event->input->getArguments(),
            'options' => $event->input->getOptions(),
        ])->setResult($event->exitCode === 0 ? 'success' : 'error');
    }
}
