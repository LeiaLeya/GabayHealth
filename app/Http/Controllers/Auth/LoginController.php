<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\FirebaseService;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $hideSidebar = true;
        return view('auth.login', compact('hideSidebar'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $firestore = app(FirebaseService::class)->getFirestore();
        $collections = [
            ['name' => 'barangay', 'role' => 'barangay'],
            ['name' => 'rhu', 'role' => 'rhu'],
            ['name' => 'admin', 'role' => 'admin'],
        ];
        $user = null;
        $userId = null;
        $userRole = null;
        $userStatus = null;
        foreach ($collections as $col) {
            $docs = $firestore->collection($col['name'])->where('username', '=', $request->username)->documents();
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    if (isset($data['password']) && \Hash::check($request->password, $data['password'])) {
                        $user = $data;
                        $userId = $doc->id();
                        $userRole = $col['role'];
                        $userStatus = $data['status'] ?? 'approved';
                        break 2;
                    }
                }
            }
        }
        if (!$user) {
            return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
        }
        if ($userStatus !== 'approved') {
            return back()->withErrors(['login' => 'Your account is not yet approved.'])->withInput();
        }
        // Store user info in session
        Session::put('user', [
            'id' => $userId,
            'role' => $userRole,
            'username' => $user['username'],
            'name' => $user['healthCenterName'] ?? $user['name'] ?? 'User',
        ]);
        // Redirect based on role
        if ($userRole === 'admin') {
            return redirect()->route('reports.index');
        } elseif ($userRole === 'rhu') {
            return redirect()->route('schedules.index');
        } elseif ($userRole === 'barangay') {
            return redirect()->route('inventory.index');
        } else {
            return redirect('/');
        }
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login');
    }
} 