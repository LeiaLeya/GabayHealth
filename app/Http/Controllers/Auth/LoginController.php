<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Cache;
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
            $account = $this->resolveAccountByUsername($firestore, $request->username);
            if (!$account) {
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            }

            $userDoc = $firestore->collection($account['collection'])->document($account['uid'])->snapshot();
            if (!$userDoc->exists()) {
                Cache::forget('auth_lookup_username:' . strtolower(trim($request->username)));
                return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
            }

            $user = $userDoc->data();
            $user['id'] = $account['uid'];
            $user['uid'] = $account['uid'];
            $userRole = $account['role'];

            // Verify password
            if (!password_verify($request->password, $user['password'] ?? '')) {
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
            \Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors(['login' => 'Login failed. Please try again.'])->withInput();
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

            $account = $this->resolveAccountByEmail($firestore, $googleUser->email);
            $user = null;
            $userRole = null;
            $userId = null;
            if ($account) {
                $userDoc = $firestore->collection($account['collection'])->document($account['uid'])->snapshot();
                if ($userDoc->exists()) {
                    $user = $userDoc->data();
                    $userRole = $account['role'];
                    $userId = $account['uid'];
                } else {
                    Cache::forget('auth_lookup_email:' . strtolower(trim($googleUser->email)));
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

            if (($user['status'] ?? 'pending') !== 'approved') {
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

    private function resolveAccountByUsername($firestore, string $username): ?array
    {
        $cacheKey = 'auth_lookup_username:' . strtolower(trim($username));
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($firestore, $username) {
            return $this->findAccount($firestore, 'username', $username);
        });
    }

    private function resolveAccountByEmail($firestore, string $email): ?array
    {
        $cacheKey = 'auth_lookup_email:' . strtolower(trim($email));
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($firestore, $email) {
            return $this->findAccount($firestore, 'email', $email);
        });
    }

    private function findAccount($firestore, string $field, string $value): ?array
    {
        $adminDocs = $firestore->collection('admin')
            ->where($field, '=', $value)
            ->documents();
        foreach ($adminDocs as $doc) {
            if ($doc->exists()) {
                return [
                    'uid' => $doc->id(),
                    'role' => 'admin',
                    'collection' => 'admin',
                ];
            }
        }

        $rhuDocs = $firestore->collection('rhu')
            ->where($field, '=', $value)
            ->documents();
        foreach ($rhuDocs as $doc) {
            if ($doc->exists()) {
                return [
                    'uid' => $doc->id(),
                    'role' => 'rhu',
                    'collection' => 'rhu',
                ];
            }
        }

        $barangayDocs = $firestore->collection('barangay')
            ->where($field, '=', $value)
            ->documents();
        foreach ($barangayDocs as $doc) {
            if ($doc->exists()) {
                return [
                    'uid' => $doc->id(),
                    'role' => 'barangay',
                    'collection' => 'barangay',
                ];
            }
        }

        return null;
    }

}