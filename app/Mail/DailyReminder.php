<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\User; 

class DailyReminder extends Mailable
{ 
    use Queueable, SerializesModels;
    
    public $value; 
    
    public function __construct($value)
    { 
        $this->value = $value;
    }
    
    public function build()
    {
        return $this->view('DailyReminder')->with('values', $this->value) ;
    }
}
