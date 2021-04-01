<?php

namespace Internexus\WatcherLaravel;

use Internexus\WatcherLaravel\Providers\DatabaseQueryServiceProvider;
use Internexus\WatcherLaravel\Providers\NotificationServiceProvider;
use Internexus\WatcherLaravel\Providers\ExceptionsServiceProvider;
use Internexus\WatcherLaravel\Providers\CommandServiceProvider;
use Internexus\WatcherLaravel\Providers\EmailServiceProvider;
use Internexus\WatcherLaravel\Providers\JobServiceProvider;
use Internexus\WatcherLaravel\Commands\TestCommand;
use Internexus\Watcher\Watcher;
use Internexus\Watcher\Config;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider;

class WatcherServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/watcher.php', 'watcher'
        );

        $this->app->singleton('watcher', function () {
            $config = new Config(config('watcher.key'), config('watcher.url'));
            $config->setEnabled(config('watcher.enable'));

            return new Watcher($config);
        });

        $this->registerServiceProviders();
    }

    /**
     * Setup configuration file.
     *
     * @return void
     */
    protected function setupConfigFile()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/watcher.php' => config_path('watcher.php')
            ], 'config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('watcher');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfigFile();

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCommand::class,
            ]);
        }
    }

    /**
     * Register all services providers.
     *
     * @return void
     */
    protected function registerServiceProviders()
    {
        if ($this->app->runningInConsole()) {
            $this->app->register(CommandServiceProvider::class);
        }

        if (config('watcher.job')) {
            $this->app->register(JobServiceProvider::class);
        }

        if (config('watcher.email')) {
            $this->app->register(EmailServiceProvider::class);
        }

        if (config('watcher.unhandled_exceptions')) {
            $this->app->register(ExceptionsServiceProvider::class);
        }

        if (config('watcher.notifications')) {
            $this->app->register(NotificationServiceProvider::class);
        }

        if(config('watcher.query')){
            $this->app->register(DatabaseQueryServiceProvider::class);
        }
    }
}
