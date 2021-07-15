<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\User;

class EmailVerification extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(User $user){
		$this->user = $user;
	}

	public function build(){
		return $this->view('verification')->with([
			'email_token' => $this->user->email_token; 
		]);
	}
}