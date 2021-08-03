<?php

namespace App\Jobs;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NewTask;

class NewTaskEmail extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $task;
    protected $user;

    public function __construct(User $user, Task $task)
    {
        //
        $this->task = $task;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $email = new NewTask($this->task);
        Mail::to($this->user->email)->send($email);
    }
}
