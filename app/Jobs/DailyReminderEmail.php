<?php

namespace App\Jobs;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\DailyReminder;

class DailyReminderEmail extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $user_id;
    protected $value;

    public function __construct($user_id, $value)
    {
        $this->user_id = $user_id;
        $this->value = $value;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new DailyReminder($this->value);
        $user = User::findOrFail($this->user_id);
        Mail::to($user)->send($email);
    }
}
