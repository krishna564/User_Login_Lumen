<?php

namespace App\Http\Controllers;
use App\Events\SendNotificationEvent;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Jobs\NewTaskEmail;
use App\Jobs\UpdateTaskEmail;
use App\Jobs\UpdateStatusEmail;
use Illuminate\Support\Facades\Mail;


class TaskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function createTask(Request $request){
        $this->validate($request,[
            'title' => 'required',
            'description' => 'required',
            'assignee' => 'required',
            'due_date' => 'required|date',
        ]);
        $task = new Task;
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->assignee = $request->input('assignee');
        $task->due_date = $request->input('due_date');

        $token = substr($request->header('Authorization'),7);
        $user = User::where('auth_token', $token)->first();
        $task->created_by = $user->username;
        $task->user_id = $user->id;

        $assignee = User::where('username', $task->assignee)->firstOrFail();
        $task->save();
        $this->dispatch(new NewTaskEmail($assignee, $task));

        return response()->json(["message" => "created Task"], 200);
    }

    public function updateTask(Request $request){
        $this->validate($request,['id'=>'required']);
        try{

            $token = substr($request->header('Authorization'),7);
            $user = User::where('auth_token', $token)->first();

            $task = Task::where('id',$request->input('id'))->firstOrFail();

            if ($task->user_id != $user->id) {
                return response()->json(['message'=>'Only creator can update'],401);
            }
            if($request->has('title')){
                $task->title = $request->input('title');
            }
            if ($request->has('description')) {
                $task->description = $request->input('description');
            }
            if ($request->has('due_date') && $request->input('due_date') !=  "") {
                $task->due_date = $request->input('due_date');
            }

            $assignee = User::where('username', $task->assignee)->firstOrFail();
            $task->save();
            $this->dispatch(new UpdateTaskEmail($assignee, $task));

            return response()->json(['message'=>'updated Task'],200);
        } 
        catch (Exception $e) {
            return response()->json(["message"=> "Not able to find the requested task"], 404);
        }
    }

    public function updateStatus(Request $request){
        $this->validate($request, [
            'id' => 'required',
            'status' => 'required',
        ]);
        try {
            $token = substr($request->header('Authorization'),7);
            $user = User::where('auth_token', $token)->first();

            $task = Task::where('id',$request->input('id'))->firstOrFail();

            if ($task->assignee != $user->username) {
                return response()->json(['message'=>'Only assignee can update status'],401);
            }
            $task->status = $request->input('status');
            $assignee = User::where('username', $task->assignee)->firstOrFail();
            $this->dispatch(new UpdateStatusEmail($assignee, $task));
            $task->save();
            
            return response()->json(['message'=>'updated the status'],200);
        } catch (Exception $e) {
            return response()->json(['message'=>'task not found'],404);
        }
    }

    public function deleteTask(Request $request, $id){
        $token = substr($request->header('Authorization'),7);
        $user = User::where('auth_token', $token)->first();

        $task = Task::where('id',$id)->firstOrFail();
        if($task->user_id != $user->id){
            return response()->json(['message'=>'only creator can delete'],401);
        }

        $task->delete();
        return response()->json(['message'=>'deleted task'], 200);
    }

    public function multipleDelete(Request $request, $id){
        $ids = explode(',', $id);
        foreach ($ids as $key) {
            $task = Task::where('id', $key)->firstOrFail();
            $task->delete();
        }
        return response()->json(['message' => 'all Tasks deleted']);
    }

    public function createList(Request $request){
        $token = substr($request->header('Authorization'),7);
        $user = User::where('auth_token', $token)->first();

        $tasks = DB::table('tasks')->where('created_by', '=', $user->username)->get();
        return response()->json(['tasks'=> $tasks],200);
    }
    public function assigneeList(Request $request){
        $token = substr($request->header('Authorization'),7);
        $user = User::where('auth_token', $token)->first();

        $tasks = DB::table('tasks')->where('assignee', '=', $user->username)->get();
        return response()->json(['tasks'=> $tasks],200);
    }
    public function allTasks(){
        $tasks = DB::table('tasks')->get();
        return response()->json(['tasks'=>$tasks],200);
    }
    public function filter(Request $request){
        $this->validate($request,[
            'method' => 'required',
            'value' => 'required',
        ]);
        if ($request->has('type')) {
            $token = substr($request->header('Authorization'), 7);
            $user = User::where('auth_token',$token)->first();
            if($request->input('method') == 'keyword'){
                $value = '%'.$request->input('value').'%';
                $tasks = DB::table('tasks')->where('description', 'like', $value)->orWhere('title', 'like', $value)->orWhere('assignee', 'like', $value)->orWhere('created_by', 'like', $value)->where($request->input('type'),'=',$user->username)->get();
                return response()->json(['tasks'=>$tasks],200);
            }
            $tasks = DB::table('tasks')->where($request->input('method'), '=', $request->input('value'))->where($request->input('type'),'=',$user->username)->get();
            return response()->json(['tasks'=>$tasks],200);
        }
        if($request->input('method') == 'keyword'){
            $value = '%'.$request->input('value').'%';
            $tasks = DB::table('tasks')->where('description', 'like', $value)->orWhere('title', 'like', $value)->orWhere('assignee', 'like', $value)->orWhere('created_by', 'like', $value)->get();
            return response()->json(['tasks'=>$tasks],200);
        }
        $tasks = DB::table('tasks')->where($request->input('method'), '=', $request->input('value'))->get();
        return response()->json(['tasks'=>$tasks],200);
    }
    public function filterDate(Request $request){
        $this->validate($request,[
            'from' => 'required',
            'to' => 'required',
        ]);
        if ($request->has('type')) {
            $token = substr($request->header('Authorization'), 7);
            $user = User::where('auth_token',$token)->first();
            $tasks = DB::table('tasks')->whereBetween('due_date',[$request->input('from'), $request->input('to')])->where($request->input('type'),'=',$user->username)->get();
        }
        $tasks = DB::table('tasks')->whereBetween('due_date',[$request->input('from'), $request->input('to')])->get();
        return response()->json(['tasks'=>$tasks],200);
    }
}