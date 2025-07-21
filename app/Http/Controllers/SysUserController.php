<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SysUserController extends Controller
{
    public function login()
    {
        if (Session::has('user')) {
            return redirect()->route('RHUs.index')->with('success', 'Login successful.');
        }
        return view('auth.login');
    }

    public function authenticate(Request $request, FirestoreService $firestore)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        
        $users = $firestore->db->collection('admin')->where('username', '=', $credentials['username'])->documents();
        foreach ($users as $userDoc) {
            if ($userDoc->exists()) {
                $user = $userDoc->data();
                
                if (Hash::check($credentials['password'], $user['password'])) {
                    Session::put('user', [
                        'id' => $userDoc->id(),
                        'username' => $user['username'],
                    ]);
                    return redirect()->route('RHUs.index')->with('success', 'Login successful.');
                }
            }
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ])->withInput();
    }

    public function register()
    {
        return view('auth.register');
    }

    public function store(Request $request, FirestoreService $firestore)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6|confirmed',
            // 'email' => 'nullable|email', 
        ]);

        
        $existing = $firestore->db->collection('admin')->where('username', '=', $validated['username'])->documents();
        if (iterator_count($existing) > 0) {
            return back()->withErrors(['username' => 'Username already taken.'])->withInput();
        }

        $firestore->addDocument('admin', [
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            // 'email' => $validated['email'] ?? '',
            // 'createdAt' => now()->toDateTimeString(),
            // 'userId' => '', 
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}