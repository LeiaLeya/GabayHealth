<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\FirebaseService;
use App\Helpers\PasswordHelper;

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

        \Log::info('Login attempt for username: ' . $request->username);

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
            \Log::info('Checking collection: ' . $col['name']);
            $docs = $firestore->collection($col['name'])->where('username', '=', $request->username)->documents();
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    \Log::info('Found user in ' . $col['name'] . ' with ID: ' . $doc->id());
                    if (isset($data['password'])) {
                        $passwordValid = false;
                        
                        // For admin accounts, handle both bcrypt and plain text passwords
                        if ($col['role'] === 'admin') {
                            // First try bcrypt verification
                            if (\Hash::check($request->password, $data['password'])) {
                                $passwordValid = true;
                                \Log::info('Admin password verified with bcrypt');
                            }
                            // If bcrypt fails, check if it's plain text (for existing accounts)
                            elseif ($request->password === $data['password']) {
                                $passwordValid = true;
                                \Log::info('Admin password verified with plain text');
                                // Update the password to bcrypt format for future logins
                                $firestore->collection($col['name'])->document($doc->id())->update([
                                    'password' => bcrypt($request->password)
                                ]);
                            }
                        } else {
                            // For non-admin accounts, only use bcrypt
                            $passwordValid = \Hash::check($request->password, $data['password']);
                            \Log::info('Password verification result: ' . ($passwordValid ? 'true' : 'false'));
                        }
                        
                        if ($passwordValid) {
                            $user = $data;
                            $userId = $doc->id();
                            $userRole = $col['role'];
                            $userStatus = $data['status'] ?? 'approved';
                            \Log::info('User authenticated successfully. Role: ' . $userRole . ', Status: ' . $userStatus);
                            break 2;
                        }
                    }
                }
            }
        }
        if (!$user) {
            \Log::info('Login failed - Invalid username or password');
            return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
        }
        if ($userStatus !== 'approved') {
            \Log::info('Login failed - Account not approved. Status: ' . $userStatus);
            return back()->withErrors(['login' => 'Your account is not yet approved.'])->withInput();
        }
        // Store user info in session
        $sessionData = [
            'id' => $userId,
            'role' => $userRole,
            'username' => $user['username'],
            'name' => $user['healthCenterName'] ?? $user['name'] ?? 'User',
            'barangayId' => $userRole === 'barangay' ? $userId : null, // Store barangayId for barangay users
        ];
        Session::put('user', $sessionData);
        
        \Log::info('Session data stored: ' . json_encode($sessionData));
        
        // Check if there's an intended URL to redirect to
        $intendedUrl = Session::get('intended_url');
        if ($intendedUrl) {
            Session::forget('intended_url');
            \Log::info('Redirecting to intended URL: ' . $intendedUrl);
            return redirect($intendedUrl);
        }
        
        // Redirect based on role
        if ($userRole === 'admin') {
            \Log::info('Redirecting admin to admin.rhus.index');
            return redirect()->route('admin.rhus.index');
        } elseif ($userRole === 'rhu') {
            \Log::info('Redirecting RHU to schedules.index');
            return redirect()->route('schedules.index');
        } elseif ($userRole === 'barangay') {
            \Log::info('Redirecting barangay to reports.index');
            return redirect()->route('reports.index');
        } else {
            \Log::info('Redirecting to home');
            return redirect('/');
        }
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login');
    }
} 