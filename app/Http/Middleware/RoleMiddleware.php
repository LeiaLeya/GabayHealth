<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\JwtAuth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is logged in
        if (!Session::has('user') || !Session::has('auth_token')) {
            Session::put('intended_url', $request->url());
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $tokenPayload = JwtAuth::decode(Session::get('auth_token'));
        $userRole = strtolower((string) ($tokenPayload['role'] ?? ''));

        if ($userRole === '') {
            Session::flush();
            return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
        }

        // Map role names for flexibility
        $roleMap = [
            'barangay' => ['barangay', 'bhc', 'bhc'],
            'rhu' => ['rhu', 'rural-health-unit'],
            'health-worker' => ['health-worker', 'hw', 'bhw'],
            'admin' => ['admin']
        ];

        // Check if user role matches any of the allowed roles
        $allowed = false;
        foreach ($roles as $role) {
            $role = strtolower($role);
            
            // Direct match
            if ($userRole === $role) {
                $allowed = true;
                break;
            }
            
            // Check role map
            foreach ($roleMap as $key => $variants) {
                if (in_array($role, $variants) && $userRole === $key) {
                    $allowed = true;
                    break 2;
                }
            }
        }

        if (!$allowed) {
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

