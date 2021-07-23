<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Auth;

class PasswordController extends Controller
{

    public function __construct()
    {
        // $this->broker = 'users';
    }

    public function postEmail(Request $request){
		return $this->sendResetLinkEmail($request);
	}

	public function sendResetLinkEmail(Request $request)
	{
		$this->validate($request, ['email' => 'required|email']);

		// $broker = $this->getBroker();
		// return response()->json(['broker' => $broker]);

		$response = Password::broker('users')->sendResetLink($request->only('email'));
        switch ($response) {
        	case Password::RESET_LINK_SENT:
        		return $this->getSendResetLinkEmailSuccessResponse($response);
        	case Password::INVALID_USER:
        	default:
        		return $this->getSendResetLinkEmailFailureRespone($response);
        }

		
	}

	protected function getSendResetLinkEmailSuccessResponse($response){
		return response()->json(["success" => true]);
	}

	protected function getSendResetLinkEmailFailureRespone($response){
		return response()->json(["success" => false], 400);
	}

	public function postReset(Request $request){
		return $this->reset($request);
	}

	public function reset(Request $request)
	{
		$this->validate($request, [
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed|
			               min:8|
			               regex:/^.*(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@&^*$#()_?><{}\-%]).*$/',
		]);

		$credentials = $request->only('email', 'password', 'password_confirmation', 'token');

		// $broker = $this->getBroker();

		$response = Password::broker('users')->reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });
        // dd($response);
        switch ($response) {
        	case Password::PASSWORD_RESET:
        		return $this->getResetSuccessRespone($response);
        	
        	default:
        		return $this->getResetFailureRespone($request,$response);
        }
	}

	protected function resetPassword($user,$password)
	{
		$user->password = app('hash')->make($password);
		$user->save();
		return response()->json(['success' => true]);
	}

	protected function getResetSuccessRespone($response)
	{
		return response()->json(['success' => true]);
	}

	protected function getResetFailureRespone(Request $request, $response)
	{
		return response()->json(['success' => false],400);
	}

	// public function getBroker(){
	// 	return property_exists($this, 'broker') ? $this->broker : null;;
	// }
}