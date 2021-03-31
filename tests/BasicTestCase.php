<?php

namespace Internexus\WatcherLaravel\Tests;

use Internexus\WatcherLaravel\WatcherServiceProvider;
use Internexus\Watcher\Facades\Watcher;
use Orchestra\Testbench\TestCase;

class BasicTestCase extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            WatcherServiceProvider::class
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Watcher' => Watcher::class,
        ];
    }
}