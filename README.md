# Internexus Watcher

PHP code execution monitoring package for Laravel.

### Install

```
composer require internexus/watcher-laravel
```

#### Publish config

```
php artisan vendor:publish --provider="Internexus\WatcherLaravel\WatcherServiceProvider"
```

#### Middleware

Attach the WebMonitoringMiddleware on `app/Http/Kernel.php`:

```php
/**
 * The application's route middleware groups.
 *
 * @var array
 */
protected $middlewareGroups = [
    'web' => [
        ...,
        \Internexus\WatcherLaravel\Middleware\WebRequestMonitoring::class,
    ],

    'api' => [
        ...,
        \Internexus\WatcherLaravel\Middleware\WebRequestMonitoring::class,
    ]
```
#### For Lumen

```php
$app->register(\Internexus\WatcherLaravel\WatcherServiceProvider::class);
```

### Configure the .env variable

```
WATCHER_API_KEY=[project token]
```

#### Check your environment
```
php artisan watcher:test
```
