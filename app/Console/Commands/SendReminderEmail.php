<?php

namespace App\Console\Commands;
use App\Models\User;
use App\Models\Task;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Jobs\DailyReminderEmail;

class SendReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send email notification to users about tasks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now()->toDateString();
        $tasks = DB::table('tasks')->where('due_date','>',$date)->where('status','!=','completed')->orderBy('user_id')->whereNull('deleted_at')->get();
        $data = [];
        foreach ($tasks as $task) {
            $data[$task->user_id][] = $task;
        }
        foreach ($data as $user_id => $value) {
            dispatch(new DailyReminderEmail($user_id, $value));
        }
    }
}
