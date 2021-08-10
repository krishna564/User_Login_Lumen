<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use App\Models\Task;
use App\Jobs\UpdateStatusEmail;

class MultiUpdateStatus extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $id;
    protected $status;

    public function __construct($id, $status)
    {
        //
        $this->id = $id;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        
        foreach ($this->id as $key) {
            $task = Task::where('id', $key)->firstOrFail();
            $task->status = $this->status;
            $assignee = User::where('username',$task->assignee)->firstOrFail();
            $task->save();
            dispatch(new UpdateStatusEmail($assignee,$task));
        }
        return response()->json(['message'=>'updated all Tasks'], 200);
    }
}
