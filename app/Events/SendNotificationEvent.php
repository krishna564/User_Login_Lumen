<?php

namespace App\Events;

// use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Queue\SerializesModels;
// use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendNotificationEvent implements ShouldBroadcast
{
  use  InteractsWithSockets, SerializesModels;

  public $message;

  public function __construct($message)
  {
      $this->message = $message;
      // dd($message);  
  }

  public function broadcastOn()
  {
      return new channel('my-channel');
  }

  public function broadcastAs()
  {
      return 'my-event';
  }
}