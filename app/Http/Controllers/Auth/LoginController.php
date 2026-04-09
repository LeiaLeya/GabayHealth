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

        try {
            $firebaseService = app(FirebaseService::class);
            $firestore = $firebaseService->getFirestore();
            $auth = $firebaseService->getAuth();

            $user = null;
            $userRole = null;
            $email = null;

            $adminDocs = $firestore->collection('admin')
                ->where('username', '=', $request->username)
                ->documents();

            foreach ($adminDocs as $doc) {
                if ($doc->exists()) {
                    $user = $doc->data();
                    $user['id'] = $doc->id();
                    $userRole = 'admin';
                    $email = $user['email'];
                    break;
                }
            }

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
                        $email = $user['email'];
                        break;
                    }
                }
            }

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
                        $email = $user['email'];
                        break;
                    }
                }
            }

            if (!$user) {
                \Log::warning('Login failed: User not found', ['username' => $request->username]);
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            }

            try {
                $signInResult = $auth->signInWithEmailAndPassword($email, $request->password);
                \Log::info('Login successful via Firebase Auth', ['username' => $request->username, 'email' => $email]);
            } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
                \Log::warning('Login failed: Invalid password', ['username' => $request->username, 'email' => $email]);
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
                \Log::warning('Login failed: User not found in Firebase Auth', ['username' => $request->username, 'email' => $email]);
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            }

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

            \Log::info('User session created after login', [
                'user_id' => $user['uid'] ?? $user['id'],
                'role' => $userRole,
                'logo_url' => $user['logo_url'] ?? 'NOT SET',
            ]);

            return redirect()->route('dashboard')->with('success', 'Login successful!');
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage() . '\nStack: ' . $e->getTraceAsString());
            return back()->withErrors(['login' => 'Login failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $firebaseService = app(FirebaseService::class);
            $firestore = $firebaseService->getFirestore();

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

            if (!$user) {
                session([
                    'google_email' => $googleUser->email,
                    'google_name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'google_avatar' => $googleUser->avatar,
                ]);
                
                return redirect()->route('register.rhu.google')->with('info', 'Please complete your registration details.');
            }

            if (($user['status'] ?? 'pending') !== 'active') {
                return redirect()->route('login')->with('error', 'Your account is pending approval. Please wait for admin approval.');
            }

            session([
                'user' => [
                    'id' => $userId,
                    'uid' => $userId,
                    'username' => $user['username'] ?? $googleUser->name,
                    'email' => $googleUser->email,
                    'name' => $user['rhuName'] ?? $user['healthCenterName'] ?? $googleUser->name,
                    'role' => $userRole,
                    'status' => $user['status'] ?? 'active',
                    'logo_url' => $user['logo_url'] ?? null,
                ]
            ]);

            \Log::info('User session created after Google login', [
                'user_id' => $userId,
                'role' => $userRole,
                'logo_url' => $user['logo_url'] ?? 'NOT SET',
            ]);

            return redirect()->route('dashboard')->with('success', 'Login successful!');

        } catch (Exception $e) {
            \Log::error('Google Login error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google sign-in failed. Please try again.');
        }
    }

    public function logout()
    {
        session()->flush();
        
        session()->invalidate();
        
        session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}