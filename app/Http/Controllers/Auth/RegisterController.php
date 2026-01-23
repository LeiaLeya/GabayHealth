<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use Laravel\Socialite\Facades\Socialite;
use Cloudinary\Cloudinary;
use Cloudinary\Uploader;
use Exception;

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
            'email' => 'required|email|max:255|unique:rhu,email',
            'rhuName' => 'required|string|max:255',
            'fullAddress' => 'required|string|max:255',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'terms' => 'required|accepted',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();

        try {
            // Generate a unique ID for the RHU document
            $uid = \Str::uuid();

            // UPLOAD TO CLOUDINARY
            $logoUrl = null;
            if ($request->hasFile('logo')) {
                try {
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                            'api_key' => env('CLOUDINARY_API_KEY'),
                            'api_secret' => env('CLOUDINARY_API_SECRET'),
                        ]
                    ]);
                    $result = $cloudinary->uploadApi()->upload($request->file('logo')->getRealPath(), [
                        'folder' => "gabayhealth/rhu/{$uid}",
                        'resource_type' => 'auto',
                    ]);
                    $logoUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload error: ' . $e->getMessage());
                }
            }

            // Store RHU data with status 'pending' - credentials will be generated by System Admin
            $firestore->collection('rhu')->document($uid)->set([
                'email' => $request->email,
                'name' => $request->rhuName,
                'rhuName' => $request->rhuName,
                'fullAddress' => $request->fullAddress,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'location' => [
                    'latitude' => $request->latitude ? (float) $request->latitude : null,
                    'longitude' => $request->longitude ? (float) $request->longitude : null,
                ],
                'role' => 'rhu',
                'status' => 'pending', // Awaiting System Admin approval and credential generation
                'logo_url' => $logoUrl,
                'created_at' => now()->toDateTimeString(),
                'username' => null, // Will be generated by System Admin
                'uid' => null, // Will be created when System Admin approves
            ]);

            return back()->with('success', 'RHU registration submitted successfully! Please wait for admin approval and credentials via email.');
        } catch (\Exception $e) {
            \Log::error('Error during RHU registration: ' . $e->getMessage());
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    // Google OAuth redirect
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Google OAuth callback
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $firebaseService = app(FirebaseService::class);
            $firestore = $firebaseService->getFirestore();

            // Check if user already exists
            $existingUsers = $firestore->collection('rhu')
                ->where('email', '=', $googleUser->email)
                ->documents();

            foreach ($existingUsers as $doc) {
                if ($doc->exists()) {
                    return redirect('/dashboard')->with('success', 'Welcome back!');
                }
            }

            // Store Google data in session
            session([
                'google_email' => $googleUser->email,
                'google_name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
            ]);

            // Redirect to the simplified registration form
            return redirect()->route('register.rhu.google');
        } catch (Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect('/register/rhu')->with('error', 'Google sign-in failed');
        }
    }

    // New method to show Google registration form
    public function showGoogleForm()
    {
        // If no Google session data, redirect back to register
        if (!session('google_email')) {
            return redirect()->route('register.rhu');
        }
        return view('auth.register_rhu_google');
    }

    // Handle RHU Officer registration via Google
    public function registerRhuGoogle(Request $request)
    {
        $request->validate([
            'rhuName' => 'required|string',
            'fullAddress' => 'required|string',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();

        try {
            // Generate a unique ID for the RHU document
            $uid = Str::uuid();

            // UPLOAD TO CLOUDINARY
            $logoUrl = null;
            if ($request->hasFile('logo')) {
                try {
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                            'api_key' => env('CLOUDINARY_API_KEY'),
                            'api_secret' => env('CLOUDINARY_API_SECRET'),
                        ]
                    ]);
                    $result = $cloudinary->uploadApi()->upload($request->file('logo')->getRealPath(), [
                        'folder' => "gabayhealth/rhu/{$uid}",
                        'resource_type' => 'auto',
                    ]);
                    $logoUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload error: ' . $e->getMessage());
                }
            }

            // Store RHU data with status 'pending' - credentials will be generated by System Admin
            $firestore->collection('rhu')->document($uid)->set([
                'email' => session('google_email'),
                'rhuName' => $request->rhuName,
                'fullAddress' => $request->fullAddress,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'location' => [
                    'latitude' => $request->latitude ? (float) $request->latitude : null,
                    'longitude' => $request->longitude ? (float) $request->longitude : null,
                ],
                'role' => 'rhu',
                'status' => 'pending', // Awaiting System Admin approval and credential generation
                'logo_url' => $logoUrl,
                'google_id' => session('google_id'),
                'created_at' => now()->toDateTimeString(),
                'username' => null, // Will be generated by System Admin
                'uid' => null, // Will be created when System Admin approves
            ]);

            session()->forget(['google_email', 'google_name', 'google_id', 'google_avatar']);

            return redirect()->route('register.landing')->with('success', 'Registration submitted successfully! Please wait for admin approval and credentials via email.');
        } catch (Exception $e) {
            \Log::error('Google RHU Registration error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}