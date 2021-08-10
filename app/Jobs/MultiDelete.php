<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Task;

class MultiDelete extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $id;

    public function __construct($id)
    {
        //
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $ids = explode(',', $this->id);
        foreach ($ids as $key) {
            $task = Task::where('id', $key)->firstOrFail();
            $task->delete();
        }
        return response()->json(['message' => 'all Tasks deleted']);
    }
}
