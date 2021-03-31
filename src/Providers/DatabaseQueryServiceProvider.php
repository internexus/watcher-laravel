<?php

namespace Internexus\WatcherLaravel\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;

class DatabaseQueryServiceProvider extends ServiceProvider
{
    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(QueryExecuted::class, function (QueryExecuted $query) {
            if ($this->app['watcher']->isCapturing()) {
                $this->handleQuery($query->sql, $query->bindings, $query->time, $query->connectionName);
            }
        });
    }

    /**
     * Attach SQL query to monitoring queue.
     *
     * @param  string  $sql
     * @param  array   $bindings
     * @param  int     $time
     * @param  string  $connections
     */
    protected function handleQuery($sql, array $bindings, $time, $connection)
    {
        $segment = $this->app['watcher']->segment($connection, substr($sql, 0, 50))
            ->start(microtime(true) - $time/1000);

        $context = [
            'sql' => $sql,
            'connection' => $connection,
        ];

        if (config('watcher.bindings')) {
            $context['bindings'] = $bindings;
        }

        $segment->addContext('db', $context)->stop($time);
    }
}
