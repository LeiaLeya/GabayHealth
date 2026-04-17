<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in (has session data)
        if (!Session::has('user')) {
            // Store the intended URL to redirect back after login
            Session::put('intended_url', $request->url());
            
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        return $next($request);
    }
} 