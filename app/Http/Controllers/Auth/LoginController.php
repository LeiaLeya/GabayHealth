<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

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
        $auth = $firebaseService->getAuth();

        try {
            // Search for user by username in all collections (rhu, barangay)
            $rhuDocs = $firestore->collection('rhu')
                ->where('username', '=', $request->username)
                ->documents();

            $user = null;
            $userRole = null;

            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $user = $doc->data();
                    $user['id'] = $doc->id();
                    $user['uid'] = $doc->id();
                    $userRole = 'rhu';
                    break;
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
                ]
            ]);

            return redirect()->route('dashboard')->with('success', 'Login successful!');
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors(['login' => 'Login failed. Please try again.'])->withInput();
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}