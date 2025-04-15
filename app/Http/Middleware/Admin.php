<?php

namespace App\Http\Middleware;

use App\Models\Config;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $frank = User::find(2);
            $thijs = User::find(39);
            $admin = Config::select('admin')->first();

            if (Auth::user()->id == 2 && $frank->name == 'Frank Lambregts' || Auth::user()->id == 39 && $thijs->name == 'Thijs van Tilborg') {
                return $next($request);
            } else {
                if (Auth::user()->id !== $admin->admin) {
                    return redirect('/')->with('error', 'Je bent geen Administrator!');
                }
            }

            return $next($request);
        } else {
            return redirect('/')->with('error', 'Je bent niet ingelogd!');
        }
    }
}
