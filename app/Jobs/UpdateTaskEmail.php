<?php

namespace App\Jobs;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UpdateTask;

class UpdateTaskEmail extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $user;
    protected $task;

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
        $email = new UpdateTask($this->task);
        Mail::to($this->user->email)->send($email);
    }
}
