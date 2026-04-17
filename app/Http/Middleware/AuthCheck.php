<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\JwtAuth;
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
        if (!Session::has('user') || !Session::has('auth_token')) {
            // Store the intended URL to redirect back after login
            Session::put('intended_url', $request->url());
            
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $tokenPayload = JwtAuth::decode(Session::get('auth_token'));
        $sessionUser = Session::get('user', []);
        $sessionUserId = (string) ($sessionUser['uid'] ?? $sessionUser['id'] ?? '');

        if (
            !$tokenPayload ||
            empty($tokenPayload['sub']) ||
            (string) $tokenPayload['sub'] !== $sessionUserId
        ) {
            Session::flush();
            return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
        }

        return $next($request);
    }
} 