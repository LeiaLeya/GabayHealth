<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RHUAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user') || Session::get('user.role') !== 'rhu') {
            return redirect()->route('login')->with('error', 'RHU access required.');
        }

        return $next($request);
    }
}