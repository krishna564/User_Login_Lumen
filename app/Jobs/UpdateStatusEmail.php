<?php

namespace App\Jobs;
use App\Models\User;
use App\Models\Task;
use App\Mail\UpdateStatus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateStatusEmail extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $user;
    protected $task;
    public $attemps=2;

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
        $email = new UpdateStatus($this->task);
        // dd($this->task);
        Mail::to($this->user->email)->send($email);
    }
}
