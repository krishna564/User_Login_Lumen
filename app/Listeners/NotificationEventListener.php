<?php

namespace App\Listeners;

use App\Events\SendNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Pusher\Pusher;

class NotificationEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(SendNotificationEvent $event)
    {
        //
       $app_id = env('PUSHER_APP_ID');
       $app_key = env('PUSHER_APP_KEY');
       $app_secret = env('PUSHER_APP_SECRET');
       $app_cluster = env('PUSHER_APP_CLUSTER');
       $options = [
          'cluster' => $app_cluster,
          'useTLS' => true
        ];

       $pusher = new Pusher($app_key, $app_secret, $app_id, $options);
       $pusher->trigger('my-channel', 'my-event', $event->message);
    }
}
