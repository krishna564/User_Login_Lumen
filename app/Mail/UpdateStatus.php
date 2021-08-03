<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Task; 

class UpdateStatus extends Mailable
{ 
    use Queueable, SerializesModels;
    
    public $task; 
    
    public function __construct(Task $task)
    { 
        $this->task = $task;
    }
    
    public function build()
    {
        // dd($this->task);
        return $this->view('UpdateStatus');
    }
}
