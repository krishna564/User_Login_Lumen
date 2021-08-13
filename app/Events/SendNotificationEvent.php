<?php

namespace App\Events;

// use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendNotificationEvent extends Event
{
  use SerializesModels;

  public $message;
  public $username;

  public function __construct($message,$username)
  {
      $this->message = $message;
      $this->username = $username;
  }

}