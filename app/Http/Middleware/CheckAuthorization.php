<?php

namespace App\Http\Middleware;
use App\Models\User;

use Closure;

class CheckAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = substr($request->header('Authorization'), 7);
        $user = User::where('auth_token', $token)->first();
        if ($user['roles'] != 'Admin') {
            return response()->json(["message" => "Not Authorized"], 401);
        }

        return $next($request);
    }
}