<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use App\Models\Config;

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
            $admin = Config::select('Admin')->first();
            if (Auth::user()->id !== $admin->Admin) {
                return redirect('/')->with('error', 'Je bent geen Administrator!');
            }
            return $next($request);
        } else {
            return redirect('/')->with('error', 'Je bent niet ingelogd!');
        }
    }
}
