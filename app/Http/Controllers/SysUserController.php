<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\SysUser;
use Illuminate\Support\Facades\Auth;

class SysUserController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('RHUs.index')->with('success', 'Login successful.');
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('RHUs.index')->with('success', 'Login successful.');
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ])->withInput();
    }

    public function register()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:sys_users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = SysUser::create([
            'username' => $validated['username'],
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('RHUs.index')->with('success', 'Registration successful!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
