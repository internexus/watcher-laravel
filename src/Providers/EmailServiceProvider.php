<?php

namespace Internexus\WatcherLaravel\Providers;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;

use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(MessageSending::class, function (MessageSending $event) {
            if ($this->app['watcher']->isCapturing()) {
                $this->handleMessageSending($event);
            }
        });

        $this->app['events']->listen(MessageSent::class, function (MessageSent $event) {
            $this->handleMessageSent($event);
        });
    }

    /**
     * Handle message sent event.
     *
     * @param  MessageSent  $event
     * @return void
     */
    protected function handleMessageSent(MessageSent $event)
    {
        $key = $this->getSegmentKey($event->message);

        if (array_key_exists($key, $this->segments)) {
            $this->segments[$key]->stop();
        }
    }

    /**
     * Handle message sending event.
     *
     * @param  MessageSending  $event
     * @return void
     */
    protected function handleMessageSending(MessageSending $event)
    {
        $key = $this->getSegmentKey($event->message);
        $data = property_exists($event, 'data') ? $event->data : null;
        $segment = $this->app['watcher']->segment('email', get_class($event->message))->addContext('data', $data);

        $this->segments[$key] = $segment;
    }

    /**
     * Generate a unique key for each message.
     *
     * @param \Swift_Message $message
     * @return string
     */
    protected function getSegmentKey(\Swift_Message $message)
    {
        return sha1(trim($message->getHeaders()->get('Content-Type')->toString()));
    }
}
