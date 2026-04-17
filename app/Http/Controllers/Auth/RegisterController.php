<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        try {
            $firestore = app(\App\Services\FirebaseService::class)->getFirestore();
            
            // Fetch all RHUs first to debug
            $allRhuDocs = $firestore->collection('rhu')->documents();
            $allRhus = [];
            foreach ($allRhuDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $allRhus[] = [
                        'id' => $doc->id(),
                        'name' => $data['name'] ?? $data['rhuName'] ?? 'Unnamed RHU',
                        'status' => $data['status'] ?? 'unknown',
                    ];
                }
            }
            \Log::info('All RHUs found:', ['count' => count($allRhus), 'rhus' => $allRhus]);
            
            // Now fetch only approved RHUs
            $rhuDocs = $firestore->collection('rhu')->where('status', '=', 'approved')->documents();
            $rhus = [];
            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $rhus[] = [
                        'id' => $doc->id(),
                        'name' => $data['name'] ?? $data['rhuName'] ?? 'Unnamed RHU',
                    ];
                }
            }
            
            \Log::info('Approved RHUs found:', ['count' => count($rhus), 'rhus' => $rhus]);
            
            // If no approved RHUs, log a warning
            if (empty($rhus)) {
                \Log::warning('No approved RHUs found. BHW registration form will show empty dropdown.');
            }
            
            return view('auth.register_bhw', compact('rhus'));
        } catch (\Exception $e) {
            \Log::error('Error fetching RHUs for BHW registration: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            // Return empty array so form still loads
            $rhus = [];
            return view('auth.register_bhw', compact('rhus'))->with('warning', 'Unable to load RHU list. Please contact administrator.');
        }
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'terms' => 'required|accepted',
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
                        'folder' => "gabayhealth/barangay/{$uid}",
                        'resource_type' => 'auto',
                    ]);
                    $logoUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload error: ' . $e->getMessage());
                }
            }

            // Build location object: { latitude, longitude, name }
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $location = [
                'latitude' => ($lat !== null && $lat !== '') ? (float) $lat : null,
                'longitude' => ($lng !== null && $lng !== '') ? (float) $lng : null,
                'name' => $request->fullAddress ?? '',
            ];

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
                'logo_url' => $logoUrl,
                'location' => $location,
                'created_at' => now()->toDateTimeString(),
            ]);

            $this->saveUserProfile($firestore, $uid, $email, 'barangay', $uid, $request->healthCenterName);

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
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'username' => 'required|string|max:255',
            'rhuName' => 'required|string|max:255',
            'fullAddress' => 'required|string|max:255',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'terms' => 'required|accepted',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        try {
            $existingUsername = $firestore->collection('rhu')
                ->where('username', '=', $request->username)
                ->documents();

            foreach ($existingUsername as $doc) {
                if ($doc->exists()) {
                    return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
                }
            }

            $email = $request->email;
            
            $authUser = $auth->createUser([
                'email' => $email,
                'password' => $request->password,
                'displayName' => $request->rhuName,
                'emailVerified' => false,
            ]);

            $uid = $authUser->uid;

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

            // Build location object: { latitude, longitude, name }
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $location = [
                'latitude' => ($lat !== null && $lat !== '') ? (float) $lat : null,
                'longitude' => ($lng !== null && $lng !== '') ? (float) $lng : null,
                'name' => $request->fullAddress ?? $request->rhuName ?? '',
            ];

            $firestore->collection('rhu')->document($uid)->set([
                'username' => $request->username,
                'email' => $email,
                'uid' => $uid,
                'password' => bcrypt($request->password),
                'name' => $request->rhuName,
                'healthCenterName' => $request->rhuName,
                'fullAddress' => $request->fullAddress,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'role' => 'rhu',
                'status' => 'pending',
                'logo_url' => $logoUrl,
                'location' => $location,
                'created_at' => now()->toDateTimeString(),
            ]);

            $this->saveUserProfile($firestore, $uid, $email, 'rhu', $uid, $request->rhuName);

            return back()->with('success', 'RHU registration submitted! Waiting for admin approval.');
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            \Log::error('Firebase Auth: Email already exists - ' . $e->getMessage());
            return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
        } catch (\Exception $e) {
            \Log::error('Error during RHU registration: ' . $e->getMessage());
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    // Google OAuth redirect for RHU
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Google OAuth redirect for BHW
    public function redirectToGoogleBhw()
    {
        session(['oauth_type' => 'bhw']);
        return Socialite::driver('google')->redirect();
    }

    // Google OAuth callback
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $oauthType = session('oauth_type', 'rhu');
            
            $firebaseService = app(FirebaseService::class);
            $firestore = $firebaseService->getFirestore();

            if ($oauthType === 'bhw') {
                // Check if user already exists in barangay collection
                $existingUsers = $firestore->collection('barangay')
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
                    'oauth_type' => 'bhw',
                ]);

                // Redirect to the simplified registration form
                return redirect()->route('register.bhw.google');
            } else {
                // RHU flow
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
                    'oauth_type' => 'rhu',
                ]);

                // Redirect to the simplified registration form
                return redirect()->route('register.rhu.google');
            }
        } catch (Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            $oauthType = session('oauth_type', 'rhu');
            session()->forget('oauth_type');
            if ($oauthType === 'bhw') {
                return redirect('/register/bhw')->with('error', 'Google sign-in failed');
            }
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
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string',
            'rhuName' => 'required|string',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        try {
            $existingUsername = $firestore->collection('rhu')
                ->where('username', '=', $request->username)
                ->documents();

            foreach ($existingUsername as $doc) {
                if ($doc->exists()) {
                    return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
                }
            }

            $authUser = $auth->createUser([
                'email' => session('google_email'),
                'password' => $request->password,
                'displayName' => session('google_name'),
                'emailVerified' => true,
            ]);

            $uid = $authUser->uid;

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

            // Build location object: { latitude, longitude, name } — Google form has no address/coords, use rhuName
            $location = [
                'latitude' => null,
                'longitude' => null,
                'name' => $request->rhuName ?? '',
            ];

            $firestore->collection('rhu')->document($uid)->set([
                'username' => $request->username,
                'email' => session('google_email'),
                'uid' => $uid,
                'password' => bcrypt($request->password),
                'rhuName' => $request->rhuName,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'role' => 'rhu',
                'status' => 'pending',
                'logo_url' => $logoUrl,
                'location' => $location,
                'google_id' => session('google_id'),
                'created_at' => now()->toDateTimeString(),
            ]);

            $this->saveUserProfile($firestore, $uid, session('google_email'), 'rhu', $uid, $request->rhuName);

            session()->forget(['google_email', 'google_name', 'google_id', 'google_avatar']);

            return redirect()->route('dashboard')->with('success', 'Registration submitted! Waiting for admin approval.');
        } catch (Exception $e) {
            \Log::error('Google RHU Registration error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // New method to show Google registration form for BHW
    public function showGoogleFormBhw()
    {
        // If no Google session data, redirect back to register
        if (!session('google_email') || session('oauth_type') !== 'bhw') {
            return redirect()->route('register.bhw');
        }
        
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
        
        return view('auth.register_bhw_google', compact('rhus'));
    }

    // Handle BHW registration via Google
    public function registerBhwGoogle(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'username' => 'required|string',
            'healthCenterName' => 'required|string',
            'fullAddress' => 'required|string',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'barangay' => 'required|string',
            'postalCode' => 'required|string|max:20',
            'rhuId' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        try {
            $existingUsername = $firestore->collection('barangay')
                ->where('username', '=', $request->username)
                ->documents();

            foreach ($existingUsername as $doc) {
                if ($doc->exists()) {
                    return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
                }
            }

            $authUser = $auth->createUser([
                'email' => session('google_email'),
                'password' => $request->password,
                'displayName' => session('google_name'),
                'emailVerified' => true,
            ]);

            $uid = $authUser->uid;

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
                        'folder' => "gabayhealth/barangay/{$uid}",
                        'resource_type' => 'auto',
                    ]);
                    $logoUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload error: ' . $e->getMessage());
                }
            }

            // Build location object: { latitude, longitude, name } — Google form has fullAddress but no coords
            $location = [
                'latitude' => null,
                'longitude' => null,
                'name' => $request->fullAddress ?? $request->healthCenterName ?? '',
            ];

            $firestore->collection('barangay')->document($uid)->set([
                'username' => $request->username,
                'email' => session('google_email'),
                'uid' => $uid,
                'password' => bcrypt($request->password),
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
                'logo_url' => $logoUrl,
                'location' => $location,
                'google_id' => session('google_id'),
                'created_at' => now()->toDateTimeString(),
            ]);

            $this->saveUserProfile($firestore, $uid, session('google_email'), 'barangay', $uid, $request->healthCenterName);

            // Notify the selected RHU
            $firestore->collection('rhu')->document($request->rhuId)
                ->collection('notifications')->add([
                    'type' => 'barangay_registration',
                    'barangay_id' => $uid,
                    'barangay_name' => $request->healthCenterName,
                    'created_at' => now()->toDateTimeString(),
                    'status' => 'unread',
                ]);

            session()->forget(['google_email', 'google_name', 'google_id', 'google_avatar', 'oauth_type']);

            return redirect()->route('login')->with('success', 'Registration submitted! Waiting for RHU approval.');
        } catch (Exception $e) {
            \Log::error('Google BHW Registration error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function saveUserProfile($firestore, string $uid, string $email, string $role, ?string $barangayId, ?string $barangayName): void
    {
        $firestore->collection('users')->document($uid)->set([
            'uid' => $uid,
            'email' => $email,
            'role' => $role,
            'barangay_id' => $barangayId ?? '',
            'barangay_name' => $barangayName ?? '',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], ['merge' => true]);
    }
}