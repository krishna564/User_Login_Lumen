<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Support\Str;
use App\Jobs\SendVerificationEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification; 
class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $salt;

    public function __construct()
    {
        //
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyEmail']]);
    }

    //

    public function register(Request $request){

        $this->validate($request,[
            'username' => 'required|string|unique:users',
            'password' => 'required|
                           confirmed|
                           min:8|
                           regex:/^.*(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@&^*$#()_?><{}\-%]).*$/',
            'email' => 'required|email|unique:users'
        ]);

        try {
            $user = new User;

            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $plain = $request->input('password');
            $user->password = app('hash')->make($plain);
            $user->email_token = base64_encode('TOKEN:' . $request->input('email'));

            $user->save();
            $this->dispatch(new SendVerificationEmail($user));
            return response()->json(['user' => $user, 'message' => 'CREATED'], 200);

        } catch (\Exception $e) {

            return response()->json(['message' => 'User Registration Failed!',
                'error' => $e], 409);
        }

    }

    public function verifyEmail($token){
        $user = User::where('email_token', $token)->firstOrFail();

        $user->isVerified = true;

        if($user->save()){
            return response()->json([
                'message' => 'Successfully Verified',
                'isVerified' => $user->isVerified,
            ],200);
        }
    }

    public function login(Request $request){

        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {

            if(! $token = JWTAuth::attempt($this->getCredentials($request))){
                return $this->onUnauthorized();
            }
            
        } catch (JWTException $e) {
            return $this->onJwtGenerationError();
        }

        $user = User::where('email', $request->input('email'))->first();

        // if(!($user->isVerified)){
        //     return response()->json([
        //         'message' => 'Email is not Verified'
        //     ],403);
        // }
        $user->auth_token = $token;
        $user->save();

        return $this->onAuthorized($token);
    }

    protected function onUnauthorized(){
        return new JsonResponse([
            'message' => 'invalid_credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function onJwtGenerationError(){
        return new JsonResponse([
            'message' => 'could not create token'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function onAuthorized($token){
        return new JsonResponse([
            'message' => 'token_generated',
            'data' => [
                'token' => $token,
            ]
        ]);
    }

    protected function getCredentials(Request $request){
        return $request->only('email', 'password');
    }

    public function logout(Request $request)
    {

        auth()->logout();

        return response()->json(['message' => 'Successfully Logged Out']);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function singleUser($id){
        try{
            $user = User::findOrFail($id);

            return response()->json(['user' => $user], 200);
        }
        catch(\Exception $e){
            return response()->json(['message' => 'user not found!'], 404);
        }

    }
    
    public function editUser(Request $request)
    {   
        $this->validate($request, [
            'email' => 'required|email',
            'username' => 'required',
        ]);

        $user = User::where('email', $request->input('email'))->first();
        $user->username = $request->input('username');

        $user->save();

        return response()->json(['message' => 'edit success'],200);
    }

    public function deleteUser(Request $request, $id)
    {
        
        try{
            $user = User::findOrFail($id);

            $user->delete();

            return response()->json(['message' => 'Deleted the user'],200);
        }
        catch(\Exception $e){
            return response()->json(['message' => 'user not found'], 404);
        }
    }

    public function addUser(Request $request)
    {
        $this->validate($request,[
            'username' => 'required|string',
            'password' => 'required|
                           regex:/^.*(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@&^*$#()_?><{}\-%]).*$/',
            'email' => 'required|email|unique:users'
        ]);

        $token = substr($request->header('Authorization'), 7);
        $request_user = User::where('auth_token', $token)->first();

        try {
            $user = new User;

            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $plain = $request->input('password');
            $user->password = app('hash')->make($plain);
            $user->email_token = base64_encode('TOKEN:' . $request->input('email'));

            $user->created_by = $request_user->username;

            $user->save();
            
            return response()->json(['message' => 'Added User', 'user' => $user], 201);
            
        } catch (Exception $e) {
            return response()->json(['message' => 'not able to create user', 'error' => $e],400);
        }


    }

    public function allUsers()
    {
        $allUsers = DB::table('users')->paginate(10);
        return response()->json(['users' => $allUsers],200);
    }

    public function listUsers(Request $request)
    {
        $this->validate($request,[
            'method' => 'required',
            'value' => 'required'
        ]);
        $method = $request->input('method');
        $value = $request->input('value');
        $users = DB::table('users')->where($method , '=', $value)->paginate(10);
        return response()->json(['users' => $users],200);
    }

    public function selfEdit(Request $request)
    {
        $this->validate($request,[
            'username' => 'required',
            'email' => 'email|required',
        ]);
        $token = substr($request->header('Authorization'), 7);
        $user = User::where('auth_token', $token)->first();
        if($user->email != $request->input('email')){
            return response()->json(['message' => 'Can not edit other users'], 401);
        }
        $user->username = $request->input('username');
        $user->save();
        return response()->json(['message' => 'username updated'],200); 
    }

}
