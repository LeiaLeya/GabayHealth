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
        $rhuUsers = $firestore->db->collection('rhu_users')
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

        $rhuUsers = $firestore->db->collection('rhu_users')
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
        $rhuDetails = $this->getRHUDetails($userDoc->id(), $firestore);
        
        if (!$rhuDetails) {
            return back()->withErrors(['error' => 'RHU details not found. Please contact administrator.'])->withInput();
        }

        if ($rhuDetails['status'] !== 'approved') {
            $statusMessage = match($rhuDetails['status']) {
                'pending' => 'RHU not yet approved. Please wait for admin approval.',
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
            'rhuData' => $rhuDetails
        ]);
        
        return redirect()->route('BHUs.index')->with('success', 'Welcome back, ' . ($rhuDetails['name'] ?? 'RHU User') . '!');
    }

    private function getRHUDetails($userId, $firestore)
    {
        try {
            $rhuQuery = $firestore->db->collection('rhu')
                ->where('userId', '=', $userId)
                ->documents();

            foreach ($rhuQuery as $document) {
                return array_merge(['id' => $document->id()], $document->data());
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function register()
    {
        return view('auth.registerAdmin');
    }

    public function store(Request $request, FirestoreService $firestore)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        $existing = $firestore->db->collection('admin')->where('username', '=', $validated['username'])->documents();
        if (iterator_count($existing) > 0) {
            return back()->withErrors(['username' => 'Username already taken.'])->withInput();
        }

        $firestore->addDocument('admin', [
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'createdAt' => now()->toDateTimeString(),
        ]);

        return redirect()->route('login')->with('success', 'Admin registration successful! Please login.');
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}