<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use App\Models\Config;
use App\Models\User;

class Admin
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
        if (Auth::check()) {
            $frank = User::find(auth::user()->id)->first();
            $admin = Config::select('Admin')->first();
            if(Auth::user()->id == 2 && $frank->name == 'Frank Lambregts'){

            }else{
            if (Auth::user()->id !== $admin->Admin) {
                return redirect('/')->with('error', 'Je bent geen Administrator!');
            }}
            return $next($request);
        } else {
            return redirect('/')->with('error', 'Je bent niet ingelogd!');
        }
    }
}
