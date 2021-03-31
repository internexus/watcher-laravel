<?php

namespace Internexus\WatcherLaravel\Providers;

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(NotificationSending::class, function (NotificationSending $event) {
            if ($this->app['watcher']->isCapturing()) {
                $this->handleNotificationSending($event);
            }
        });

        $this->app['events']->listen(NotificationSent::class, function (NotificationSent $event) {
            $this->handleNotificationSent($event);
        });
    }

    /**
     * Handle message sent event.
     *
     * @param  NotificationSent  $event
     * @return void
     */
    protected function handleNotificationSent(NotificationSent $event)
    {
        $key = $event->notification->id;

        if (array_key_exists($key, $this->segments)) {
            $this->segments[$key]->stop();
        }
    }

    /**
     * Handle message sending event.
     *
     * @param  NotificationSending  $event
     * @return void
     */
    protected function handleNotificationSending(NotificationSending $event)
    {
        $key = $event->notification->id;
        $data = [
            'channel' => $event->channel,
            'notifiable' => get_class($event->notifiable),
        ];

        $segment = $this->app['watcher']->segment('notification', get_class($event->notification))->addContext('data', $data);

        $this->segments[$key] = $segment;
    }
}
