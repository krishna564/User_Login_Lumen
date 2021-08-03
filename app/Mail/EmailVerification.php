<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\User; 

class EmailVerification extends Mailable
{ 
    use Queueable, SerializesModels;
    
    public $user; 
    
    public function __construct(User $user)
    { 
        $this->user = $user;
    }
    
    public function build()
    {
        // $email = "Here is your email token ".$this->user->email_token." to verify your email";
        // dd($this->user);
        return $this->view('verification');
    }
}
