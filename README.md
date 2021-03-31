# Internexus Watcher

PHP monitoring package.

### Install

```
composer require internexus/watcher-php
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
WATCHER_TOKEN=[project token]
```

#### Check your environment
```
php artisan watcher:test
```
