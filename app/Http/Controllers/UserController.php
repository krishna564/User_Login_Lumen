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
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    //

    public function register(Request $request){

        $this->validate($request,[
            'username' => 'required|string',
            'password' => 'required|confirmed',
            'email' => 'required|email|unique:users'
        ]);

        try {
            $user = new User;

            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $plain = $request->input('password');
            $user->password = app('hash')->make($plain);
            $user->email_token = base64_encode('TOKEN:' . $request->input('email'));

            dispatch(new SendVerificationEmail($user));

            $user->save();


            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);

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

        try {
            $this->validate($request,[
                'email' => 'required|email',
                'password' => 'required',
            ]);
            
        } catch (ValidationException $e) {
            return $e->getResponse();
        }

        try {

            if(! $token = JWTAuth::attempt($this->getCredentials($request))){
                return $this->onUnauthorized();
            }
            
        } catch (JWTException $e) {
            return $this->onJwtGenerationError();
        }

        $user = User::where('email', $request->input('email'))->first();
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

        $token = substr($request->header('Authorization'), 7);
        $request_user = User::where('auth_token', $token)->first();

        if($request_user['roles'] != 'Admin'){
            return response()->json(['message' => 'Not Authorized']);
        }

        $user = User::where('email', $request->input('email'))->first();
        $user->username = $request->input('username');

        $user->save();

        return response()->json(['message' => 'edit success'],200);
    }

    public function deleteUser(Request $request, $id)
    {
        $token = substr($request->header('Authorization'), 7);
        $request_user = User::where('auth_token', $token)->first();

        if($request_user['roles'] != 'Admin'){
            return response()->json(['message' => 'Not Authorized']);
        }

        try{
            $user = User::findOrFail($id);

            $user->delete();

            return response()->json(['message', 'Deleted the user']);
        }
        catch(\Exception $e){
            return response()->json(['message' => 'user not found'], 404);
        }
    }

    public function addUser(Request $request)
    {
        $this->validate($request,[
            'username' => 'required|string',
            'password' => 'required|confirmed',
            'email' => 'required|email|unique:users'
        ]);

        $token = substr($request->header('Authorization'), 7);
        $request_user = User::where('auth_token', $token)->first();

        if($request_user['roles'] != 'Admin'){
            return response()->json(['message' => 'Not Authorized']);
        }

        try {
            $user = new User;

            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $plain = $request->input('password');
            $user->password = app('hash')->make($plain);
            $user->email_token = base64_encode('TOKEN:' . $request->input('email'));

            $user->created_by = $request_user->username;

            $user->save();
            
            return response()->json(['message' => 'Added User', 'user' => $user]);
            
        } catch (Exception $e) {
            return response()->json(['message' => 'not able to create user', 'error' => $e]);
        }


    }

    public function allUsers()
    {
        $allUsers = User::all();
        return response()->json(['users' => $allUsers],200);
    }

    public function listUsers($method, $value)
    {
        $users = DB::table('users')->where($method , '=', $value)->get();
        return response()->json(['users' => $users],200);
    }

}
