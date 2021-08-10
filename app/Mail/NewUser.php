<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\User; 

class NewUser extends Mailable
{ 
    use Queueable, SerializesModels;
    
    public $password; 
    public $user;
    
    public function __construct(User $user, $password)
    { 
        $this->password = $password;
        $this->user = $user;
    }
    
    public function build()
    {
        
        return $this->view('NewUser');
    }
}