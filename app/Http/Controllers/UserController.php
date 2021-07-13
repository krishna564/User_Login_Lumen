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
            'password' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        try {
            $user = new User;

            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $plain = $request->input('password');
            $user->password = app('hash')->make($plain);
            $user->api_token = Str::random(60);

            $user->save();

            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['message' => 'User Registration Failed!'], 409);
            
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

    public function me()
    {
        return response()->json(auth()->user());
    }
      
}
