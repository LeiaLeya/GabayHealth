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
        if ($user['status'] !== 'approved') {
            $statusMessage = match($user['status']) {
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
            'rhuData' => array_merge(['id' => $userDoc->id()], $user)
        ]);
        
        return redirect()->route('BHUs.index')->with('success', 'Welcome back, ' . ($user['name'] ?? 'RHU User') . '!');
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}