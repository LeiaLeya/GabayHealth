<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user') || Session::get('user.role') !== 'admin') {
            return redirect()->route('login')->with('error', 'Admin access required.');
        }

        return $next($request);
    }
}