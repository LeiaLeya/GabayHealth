<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class SysUserController extends Controller
{
    public function login()
    {
        if (Session::has('user')) {
            $userRole = Session::get('user.role');
            if ($userRole === 'admin') {
                return redirect()->route('RHUs.index')->with('success', 'Login successful.');
            } elseif ($userRole === 'rhu') {
                return redirect()->route('BHUs.index')->with('success', 'Login successful.');
            }
        }
        return view('auth.login');
    }

    public function authenticate(Request $request, FirestoreService $firestore)
    {
        $credentials = $request->validate([
            'loginField' => 'required|string',
            'password' => 'required|string'
        ]);

        $loginField = $credentials['loginField'];
        $password = $credentials['password'];

        try {
            $adminResult = $this->tryAdminAuthentication($loginField, $password, $firestore);
            if ($adminResult !== null) {
                return $adminResult;
            }

            $rhuResult = $this->tryRHUAuthentication($loginField, $password, $firestore);
            if ($rhuResult !== null) {
                return $rhuResult;
            }

            return redirect()->back()->with('error', 'Invalid credentials. Please check your login details.')->withInput();

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Login failed. Please try again.')->withInput();
        }
    }

    private function tryAdminAuthentication($loginField, $password, $firestore)
    {
        $adminUsers = $firestore->db->collection('admin')
            ->where('username', '=', $loginField)
            ->documents();
        
        foreach ($adminUsers as $userDoc) {
            if ($userDoc->exists()) {
                $user = $userDoc->data();
                
                if (Hash::check($password, $user['password'])) {
                    Session::put('user', [
                        'id' => $userDoc->id(),
                        'username' => $user['username'],
                        'loginField' => $loginField,
                        'role' => 'admin'
                    ]);
                    return redirect()->route('RHUs.index')->with('success', 'Welcome back, Admin!');
                }
            }
        }

        $adminUsers = $firestore->db->collection('admin')
            ->where('loginField', '=', $loginField)
            ->documents();
        
        foreach ($adminUsers as $userDoc) {
            if ($userDoc->exists()) {
                $user = $userDoc->data();
                
                if (Hash::check($password, $user['password'])) {
                    Session::put('user', [
                        'id' => $userDoc->id(),
                        'loginField' => $user['loginField'],
                        'role' => 'admin'
                    ]);
                    return redirect()->route('RHUs.index')->with('success', 'Welcome back, Admin!');
                }
            }
        }

        return null;
    }

    private function tryRHUAuthentication($loginField, $password, $firestore)
    {
        $rhuUsers = $firestore->db->collection('rhu')
            ->where('loginField', '=', $loginField)
            ->documents();
            
        foreach ($rhuUsers as $userDoc) {
            if ($userDoc->exists()) {
                $user = $userDoc->data();
                
                if (Hash::check($password, $user['password'])) {
                    return $this->handleRHULogin($userDoc, $user, $firestore);
                }
            }
        }

        $rhuUsers = $firestore->db->collection('rhu')
            ->where('contactNumber', '=', $loginField)
            ->documents();
            
        foreach ($rhuUsers as $userDoc) {
            if ($userDoc->exists()) {
                $user = $userDoc->data();
                
                if (Hash::check($password, $user['password'])) {
                    return $this->handleRHULogin($userDoc, $user, $firestore);
                }
            }
        }

        return null;
    }

    private function handleRHULogin($userDoc, $user, $firestore)
    {
        if ($user['status'] === 'pending') {
            // Allow login but redirect to pending page
            Session::put('user', [
                'id' => $userDoc->id(),
                'loginField' => $user['loginField'],
                'contactNumber' => $user['contactNumber'] ?? '',
                'role' => 'rhu',
                'rhuData' => array_merge(['id' => $userDoc->id()], $user)
            ]);
            
            return redirect()->route('rhu.pending');
        }
        
        if ($user['status'] !== 'approved') {
            $statusMessage = match($user['status']) {
                'rejected' => 'Your RHU registration has been rejected. Please contact administrator.',
                default => 'Your RHU account is not active. Please contact administrator.'
            };
            
            return redirect()->back()->with('error', $statusMessage)->withInput();
        }
        
        Session::put('user', [
            'id' => $userDoc->id(),
            'loginField' => $user['loginField'],
            'contactNumber' => $user['contactNumber'] ?? '',
            'role' => 'rhu',
            'rhuData' => array_merge(['id' => $userDoc->id()], $user)
        ]);
        
        return redirect()->route('BHUs.index')->with('success', 'Welcome back, ' . ($user['name'] ?? 'RHU User') . '!');
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    public function register()
    {
        return view('auth.registerAdmin');
    }

    public function store(Request $request, FirestoreService $firestore)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Check for username uniqueness in Firestore
        $existingUsername = $firestore->db->collection('admin')
            ->where('username', '=', $data['username'])
            ->documents();
        
        foreach ($existingUsername as $doc) {
            if ($doc->exists()) {
                return redirect()->back()->withErrors(['username' => 'The username has already been taken.'])->withInput();
            }
        }

        // Add additional fields
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'approved';
        $data['created_at'] = now()->format('Y-m-d H:i:s');
        $data['updated_at'] = now()->format('Y-m-d H:i:s');
        
        $firestore->db->collection('admin')->add($data);

        return redirect()->route('login')->with('success', 'Admin registered successfully. You can now log in.');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(FirestoreService $firestore)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Failed to authenticate with Google.');
        }

        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $googleId = $googleUser->getId();

        try {
            // Check if user exists in admin collection
            $adminUsers = $firestore->db->collection('admin')
                ->where('email', '=', $email)
                ->documents();
            
            foreach ($adminUsers as $userDoc) {
                if ($userDoc->exists()) {
                    $user = $userDoc->data();
                    Session::put('user', [
                        'id' => $userDoc->id(),
                        'email' => $email,
                        'name' => $user['username'] ?? $name,
                        'role' => 'admin',
                        'google_id' => $googleId
                    ]);
                    return redirect()->route('RHUs.index')->with('success', 'Welcome back, Admin!');
                }
            }

            // Check if user exists in rhu collection
            $rhuUsers = $firestore->db->collection('rhu')
                ->where('email', '=', $email)
                ->documents();
            
            foreach ($rhuUsers as $userDoc) {
                if ($userDoc->exists()) {
                    $user = $userDoc->data();
                    
                    if ($user['status'] === 'pending') {
                        Session::put('user', [
                            'id' => $userDoc->id(),
                            'email' => $email,
                            'name' => $user['name'] ?? $name,
                            'role' => 'rhu',
                            'google_id' => $googleId,
                            'rhuData' => array_merge(['id' => $userDoc->id()], $user)
                        ]);
                        return redirect()->route('rhu.pending');
                    }
                    
                    if ($user['status'] !== 'approved') {
                        $statusMessage = match($user['status']) {
                            'rejected' => 'Your RHU registration has been rejected. Please contact administrator.',
                            default => 'Your RHU account is not active. Please contact administrator.'
                        };
                        return redirect()->route('login')->with('error', $statusMessage);
                    }
                    
                    Session::put('user', [
                        'id' => $userDoc->id(),
                        'email' => $email,
                        'name' => $user['name'] ?? $name,
                        'role' => 'rhu',
                        'google_id' => $googleId,
                        'rhuData' => array_merge(['id' => $userDoc->id()], $user)
                    ]);
                    return redirect()->route('BHUs.index')->with('success', 'Welcome back, ' . ($user['name'] ?? 'RHU User') . '!');
                }
            }

            // User not found, create new admin user with Google OAuth
            $adminData = [
                'email' => $email,
                'name' => $name,
                'google_id' => $googleId,
                'status' => 'approved',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ];
            
            $docRef = $firestore->addDocument('admin', $adminData);
            
            Session::put('user', [
                'id' => $docRef->id(),
                'email' => $email,
                'name' => $name,
                'role' => 'admin',
                'google_id' => $googleId
            ]);

            return redirect()->route('RHUs.index')->with('success', 'Welcome, ' . $name . '! Your account has been created.');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login failed. Please try again.');
        }
    }
}