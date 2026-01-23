<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();

        try {
            // Search for user by username in all collections (rhu, barangay, admin)
            $user = null;
            $userRole = null;

            // Search in admin collection first
            $adminDocs = $firestore->collection('admin')
                ->where('username', '=', $request->username)
                ->documents();

            foreach ($adminDocs as $doc) {
                if ($doc->exists()) {
                    $user = $doc->data();
                    $user['id'] = $doc->id();
                    $userRole = 'admin';
                    break;
                }
            }

            // If not found in admin, search in RHU
            if (!$user) {
                $rhuDocs = $firestore->collection('rhu')
                    ->where('username', '=', $request->username)
                    ->documents();

                foreach ($rhuDocs as $doc) {
                    if ($doc->exists()) {
                        $user = $doc->data();
                        $user['id'] = $doc->id();
                        $user['uid'] = $doc->id();
                        $userRole = 'rhu';
                        break;
                    }
                }
            }

            // If not found in RHU, search in barangay
            if (!$user) {
                $barangayDocs = $firestore->collection('barangay')
                    ->where('username', '=', $request->username)
                    ->documents();

                foreach ($barangayDocs as $doc) {
                    if ($doc->exists()) {
                        $user = $doc->data();
                        $user['id'] = $doc->id();
                        $user['uid'] = $doc->id();
                        $userRole = 'barangay';
                        break;
                    }
                }
            }

            if (!$user) {
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            }

            // Verify password
            if (!password_verify($request->password, $user['password'] ?? '')) {
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            }

            // Store user in session
            session([
                'user' => [
                    'id' => $user['uid'] ?? $user['id'],
                    'uid' => $user['uid'] ?? $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'name' => $user['rhuName'] ?? $user['healthCenterName'] ?? $user['name'],
                    'role' => $userRole,
                    'status' => $user['status'] ?? 'active',
                    'logo_url' => $user['logo_url'] ?? null,
                ]
            ]);

            return redirect()->route('dashboard')->with('success', 'Login successful!');
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors(['login' => 'Login failed. Please try again.'])->withInput();
        }
    }

    // Google OAuth redirect for login
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Google OAuth callback for login
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $firebaseService = app(FirebaseService::class);
            $firestore = $firebaseService->getFirestore();

            // Search in RHU collection by email
            $rhuDocs = $firestore->collection('rhu')
                ->where('email', '=', $googleUser->email)
                ->documents();

            $user = null;
            $userRole = null;
            $userId = null;

            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $user = $doc->data();
                    $userId = $doc->id();
                    $userRole = 'rhu';
                    break;
                }
            }

            // If not found in RHU, search in barangay
            if (!$user) {
                $barangayDocs = $firestore->collection('barangay')
                    ->where('email', '=', $googleUser->email)
                    ->documents();

                foreach ($barangayDocs as $doc) {
                    if ($doc->exists()) {
                        $user = $doc->data();
                        $userId = $doc->id();
                        $userRole = 'barangay';
                        break;
                    }
                }
            }

            // User not found - redirect to registration instead of showing error
            if (!$user) {
                // Store Google data in session for registration
                session([
                    'google_email' => $googleUser->email,
                    'google_name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'google_avatar' => $googleUser->avatar,
                ]);
                
                return redirect()->route('register.rhu.google')->with('info', 'Please complete your registration details.');
            }

            // Check if account is approved
            if (($user['status'] ?? 'pending') !== 'approved') {
                return redirect()->route('login')->with('error', 'Your account is pending approval. Please wait for admin approval.');
            }

            // Login successful - store in session
            session([
                'user' => [
                    'id' => $userId,
                    'uid' => $userId,
                    'username' => $user['username'] ?? $googleUser->name,
                    'email' => $googleUser->email,
                    'name' => $user['rhuName'] ?? $user['healthCenterName'] ?? $googleUser->name,
                    'role' => $userRole,
                    'status' => $user['status'] ?? 'active',
                    'logo_url' => $user['logo_url'] ?? null,  // Add this line
                ]
            ]);

            return redirect()->route('dashboard')->with('success', 'Login successful!');

        } catch (Exception $e) {
            \Log::error('Google Login error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google sign-in failed. Please try again.');
        }
    }

    public function logout()
    {
        // Clear all session data
        session()->flush();
        
        // Invalidate the session
        session()->invalidate();
        
        // Regenerate session token
        session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}