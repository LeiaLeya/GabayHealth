<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
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

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        $collections = [
            ['name' => 'barangay', 'role' => 'barangay'],
            ['name' => 'rhu', 'role' => 'rhu'],
            ['name' => 'admin', 'role' => 'admin'],
        ];

        $user = null;
        $userId = null;
        $userRole = null;
        $userStatus = null;
        $userEmail = null;

        // First, find the user by username in Firestore to get their email
        foreach ($collections as $col) {
            \Log::info('Checking collection: ' . $col['name']);
            $docs = $firestore->collection($col['name'])->where('username', '=', $request->username)->documents();
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    \Log::info('Found user in ' . $col['name'] . ' with ID: ' . $doc->id());
                    
                    // Check if user has Firebase Auth (has email/uid)
                    if (isset($data['email']) || isset($data['uid'])) {
                        // User has Firebase Auth account
                        $userEmail = $data['email'] ?? (strtolower($request->username) . '@gabay-health.local');
                        $uid = $data['uid'] ?? $doc->id();
                        
                        try {
                            // Authenticate with Firebase Auth using REST API
                            $apiKey = env('FIREBASE_API_KEY');
                            if (!$apiKey) {
                                throw new \Exception('FIREBASE_API_KEY not configured');
                            }
                            
                            $response = Http::post("https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}", [
                                'email' => $userEmail,
                                'password' => $request->password,
                                'returnSecureToken' => true,
                            ]);
                            
                            if ($response->successful()) {
                                $authData = $response->json();
                                $firebaseUid = $authData['localId'] ?? $uid;
                                
                                \Log::info('Firebase Auth successful for UID: ' . $firebaseUid);
                                
                                // Use the data we already have from the username lookup
                                // The document ID should be the Firebase UID (since we use document($uid)->set() in registration)
                                $userId = $doc->id(); // This should be the Firebase UID
                                $user = $data; // Use the data we already fetched
                                $userRole = $col['role'];
                                $userStatus = $data['status'] ?? 'approved';
                                
                                \Log::info('User authenticated successfully. Role: ' . $userRole . ', Status: ' . $userStatus . ', UserId: ' . $userId);
                                break 2;
                            } else {
                                $errorData = $response->json();
                                \Log::warning('Firebase Auth failed: ' . ($errorData['error']['message'] ?? 'Unknown error'));
                            }
                        } catch (\Exception $e) {
                            \Log::error('Firebase Auth error: ' . $e->getMessage());
                            // Continue to check other collections or fallback
                        }
                    } else {
                        // Fallback: Old accounts without Firebase Auth (backward compatibility)
                        if (isset($data['password'])) {
                            $passwordValid = false;
                            
                            // For admin accounts, handle both bcrypt and plain text passwords
                            if ($col['role'] === 'admin') {
                                // First try bcrypt verification
                                if (\Hash::check($request->password, $data['password'])) {
                                    $passwordValid = true;
                                    \Log::info('Admin password verified with bcrypt (legacy)');
                                }
                                // If bcrypt fails, check if it's plain text (for existing accounts)
                                elseif ($request->password === $data['password']) {
                                    $passwordValid = true;
                                    \Log::info('Admin password verified with plain text (legacy)');
                                    // Update the password to bcrypt format for future logins
                                    $firestore->collection($col['name'])->document($doc->id())->update([
                                        'password' => bcrypt($request->password)
                                    ]);
                                }
                            } else {
                                // For non-admin accounts, only use bcrypt
                                $passwordValid = \Hash::check($request->password, $data['password']);
                                \Log::info('Password verified with bcrypt (legacy): ' . ($passwordValid ? 'true' : 'false'));
                            }
                            
                            if ($passwordValid) {
                                $user = $data;
                                $userId = $doc->id();
                                $userRole = $col['role'];
                                $userStatus = $data['status'] ?? 'approved';
                                \Log::info('Legacy user authenticated. Role: ' . $userRole . ', Status: ' . $userStatus);
                                break 2;
                            }
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
            'username' => $user['username'] ?? $request->username,
            'name' => $user['healthCenterName'] ?? $user['name'] ?? 'User',
            'barangayId' => $userRole === 'barangay' ? $userId : ($user['barangayId'] ?? null),
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
            \Log::info('Redirecting RHU to rhu.reports.index');
            return redirect()->route('rhu.reports.index');
        } elseif ($userRole === 'barangay') {
            \Log::info('Redirecting barangay to bhc.reports.index');
            return redirect()->route('bhc.reports.index');
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