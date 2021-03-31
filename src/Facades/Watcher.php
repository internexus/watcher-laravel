<?php

namespace Internexus\WatcherLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Watcher extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return 'watcher';
    }
}
