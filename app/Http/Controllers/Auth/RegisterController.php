<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class RegisterController extends Controller
{
    // Landing page for role selection
    public function landing()
    {
        return view('auth.register_landing');
    }

    // Show Barangay Health Worker (Barangay) registration form
    public function showBhwForm()
    {
        $firestore = app(\App\Services\FirebaseService::class)->getFirestore();
        $rhuDocs = $firestore->collection('rhu')->where('status', '=', 'approved')->documents();
        $rhus = [];
        foreach ($rhuDocs as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $rhus[] = [
                    'id' => $doc->id(),
                    'name' => $data['name'] ?? 'Unnamed RHU',
                ];
            }
        }
        \Log::info('Approved RHUs:', $rhus);
        return view('auth.register_bhw', compact('rhus'));
    }

    // Handle Barangay registration
    public function registerBhw(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'healthCenterName' => 'required|string|max:255',
            'fullAddress' => 'required|string|max:255',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'barangay' => 'required|string',
            'postalCode' => 'required|string|max:20',
            'rhuId' => 'required|string',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        try {
            // Generate email from username for Firebase Auth
            $email = strtolower($request->username) . '@gabay-health.local';
            
            // Create Firebase Auth user
            $authUser = $auth->createUser([
                'email' => $email,
                'password' => $request->password,
                'displayName' => $request->healthCenterName,
                'emailVerified' => false,
            ]);

            $uid = $authUser->uid;

            // Store user data in Firestore using Firebase UID as document ID
            $firestore->collection('barangay')->document($uid)->set([
                'username' => $request->username,
                'email' => $email,
                'uid' => $uid, // Store Firebase UID
                'password' => bcrypt($request->password), // Keep for backward compatibility
                'healthCenterName' => $request->healthCenterName,
                'fullAddress' => $request->fullAddress,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'barangay' => $request->barangay,
                'postalCode' => $request->postalCode,
                'rhuId' => $request->rhuId,
                'role' => 'barangay',
                'status' => 'pending',
                'created_at' => now()->toDateTimeString(),
            ]);

            // Notify the selected RHU (add a notification document)
            $firestore->collection('rhu')->document($request->rhuId)
                ->collection('notifications')->add([
                    'type' => 'barangay_registration',
                    'barangay_id' => $uid,
                    'barangay_name' => $request->healthCenterName,
                    'created_at' => now()->toDateTimeString(),
                    'status' => 'unread',
                ]);

            return back()->with('success', 'Barangay registration submitted! Waiting for RHU approval.');
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            \Log::error('Firebase Auth: Email already exists - ' . $e->getMessage());
            return back()->withErrors(['username' => 'This username is already registered.'])->withInput();
        } catch (\Exception $e) {
            \Log::error('Error during BHC registration: ' . $e->getMessage());
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    // Show RHU Officer registration form
    public function showRhuForm()
    {
        return view('auth.register_rhu');
    }

    // Handle RHU Officer registration
    public function registerRhu(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'rhuName' => 'required|string|max:255',
            'fullAddress' => 'required|string|max:255',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        try {
            // Generate email from username for Firebase Auth
            $email = strtolower($request->username) . '@gabay-health.local';
            
            // Create Firebase Auth user
            $authUser = $auth->createUser([
                'email' => $email,
                'password' => $request->password,
                'displayName' => $request->rhuName,
                'emailVerified' => false,
            ]);

            $uid = $authUser->uid;

            // Store user data in Firestore using Firebase UID as document ID
            $firestore->collection('rhu')->document($uid)->set([
                'username' => $request->username,
                'email' => $email,
                'uid' => $uid, // Store Firebase UID
                'password' => bcrypt($request->password), // Keep for backward compatibility
                'name' => $request->rhuName,
                'fullAddress' => $request->fullAddress,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'role' => 'rhu',
                'status' => 'pending',
                'created_at' => now()->toDateTimeString(),
            ]);

            // Optionally, notify admin (could add to an 'admin_notifications' collection)
            // $firestore->collection('admin_notifications')->add([...]);

            return back()->with('success', 'RHU registration submitted! Waiting for admin approval.');
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            \Log::error('Firebase Auth: Email already exists - ' . $e->getMessage());
            return back()->withErrors(['username' => 'This username is already registered.'])->withInput();
        } catch (\Exception $e) {
            \Log::error('Error during RHU registration: ' . $e->getMessage());
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
        }
    }
} 