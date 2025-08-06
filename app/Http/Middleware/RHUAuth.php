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

        // Check if user is pending and not on pending page
        $user = Session::get('user');
        $rhuData = $user['rhuData'] ?? [];
        
        if (($rhuData['status'] ?? '') === 'pending' && !$request->routeIs('rhu.pending')) {
            return redirect()->route('rhu.pending');
        }

        // Check if user is approved but trying to access pending page
        if (($rhuData['status'] ?? '') === 'approved' && $request->routeIs('rhu.pending')) {
            return redirect()->route('BHUs.index');
        }

        return $next($request);
    }
}