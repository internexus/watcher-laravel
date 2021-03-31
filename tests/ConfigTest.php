<?php

namespace Internexus\WatcherLaravel\Tests;

use Internexus\WatcherLaravel\Middlewares\WebRequestMonitoring;
use Internexus\Watcher\Entities\Transaction;
use Internexus\Watcher\Facades\Watcher;

class ConfigTest extends BasicTestCase
{
    public function testIsCapturing()
    {
        $this->app->router->get('test', function () {
            return Watcher::isCapturing();
        })->middleware(WebRequestMonitoring::class);

        $response = $this->get('test');

        $this->assertInstanceOf(Transaction::class, Watcher::current());
        $this->assertTrue($response->getContent() === '1');
    }
}