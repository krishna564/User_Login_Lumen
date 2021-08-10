<?php

namespace App\Jobs;
use App\Models\User;
use App\Mail\NewUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewUserEmail extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $user;
    protected $password;

    public function __construct(User $user, $password)
    {
        //
        $this->user=$user;
        $this->password=$password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $email = new NewUser($this->user, $this->password);
        Mail::to($this->user->email)->send($email);
    }
}