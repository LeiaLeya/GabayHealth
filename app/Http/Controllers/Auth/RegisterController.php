<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Services\FirebaseService;
use App\Mail\RhuRegistrationReceivedEmail;
use Laravel\Socialite\Facades\Socialite;
use Cloudinary\Cloudinary;
use Exception;

class RegisterController extends Controller
{
    public function landing()
    {
        return view('auth.register_landing');
    }

    public function showBhwForm()
    {
        try {
            $firestore = app(\App\Services\FirebaseService::class)->getFirestore();

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
            if (empty($rhus)) {
                \Log::warning('No approved RHUs found. BHW registration form will show empty dropdown.');
            }

            return view('auth.register_bhw', compact('rhus'));
        } catch (\Exception $e) {
            \Log::error('Error fetching RHUs for BHW registration: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $rhus = [];
            return view('auth.register_bhw', compact('rhus'))->with('warning', 'Unable to load RHU list. Please contact administrator.');
        }
    }

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
            $email = strtolower($request->username) . '@gabay-health.local';
            
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
                'created_at' => now()->toDateTimeString(),
            ]);

            $this->saveUserProfile(
                $firestore,
                $uid,
                $email,
                'barangay',
                $uid,
                $request->healthCenterName,
                $request->input('fullName', $request->healthCenterName)
            );

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

    public function showRhuForm()
    {
        return view('auth.register_rhu');
    }

    public function registerRhu(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
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

        $existingRhu = $firestore->collection('rhu')
            ->where('email', '=', $request->email)
            ->documents();
        
        foreach ($existingRhu as $doc) {
            if ($doc->exists()) {
                return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
            }
        }

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();

        try {
            $uid = \Str::uuid();

            $logoUrl = null;
            if ($request->hasFile('logo')) {
                try {
                    $logo = $request->file('logo');
                    \Log::info('Logo file detected', [
                        'filename' => $logo->getClientOriginalName(),
                        'size' => $logo->getSize(),
                        'mime' => $logo->getMimeType(),
                        'tmp_path' => $logo->getRealPath(),
                    ]);

                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                            'api_key'    => env('CLOUDINARY_API_KEY'),
                            'api_secret' => env('CLOUDINARY_API_SECRET'),
                        ]
                    ]);
                    
                    $result = $cloudinary->uploadApi()->upload($logo->getRealPath(), [
                        'folder' => "gabayhealth/rhu/{$uid}",
                        'resource_type' => 'auto',
                        'quality' => 'auto',
                    ]);
                    
                    $logoUrl = $result['secure_url'];
                    \Log::info('Logo uploaded to Cloudinary', [
                        'rhu_id' => $uid,
                        'logo_url' => $logoUrl,
                        'public_id' => $result['public_id'] ?? 'N/A',
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload error: ' . $e->getMessage(), [
                        'rhu_id' => $uid,
                        'file' => $request->file('logo') ? 'File present' : 'No file',
                        'exception_class' => get_class($e),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                \Log::info('No logo file uploaded for RHU', ['rhu_id' => $uid]);
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
                'status' => 'pending',
                'logo_url' => $logoUrl,
                'location' => $location,
                'created_at' => now()->toDateTimeString(),
                'username' => null,
                'uid' => null,
            ]);
            $this->initializeRhuServicesSubcollection($firestore, $uid);

            $this->saveUserProfile(
                $firestore,
                $uid,
                $request->email,
                'rhu',
                $uid,
                $request->rhuName,
                $request->input('fullName', $request->rhuName)
            );

            \Log::info('RHU registration created', [
                'rhu_id' => $uid,
                'rhu_name' => $request->rhuName,
                'logo_url' => $logoUrl,
            ]);

            try {
                Mail::to($request->email)->send(new RhuRegistrationReceivedEmail($request->rhuName));
            } catch (\Exception $mailException) {
                \Log::error('Failed to send registration confirmation email: ' . $mailException->getMessage());
            }

            return back()->with('success', 'RHU registration submitted successfully! Please check your email and wait for admin approval.');
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
                $existingUsers = $firestore->collection('barangay')
                    ->where('email', '=', $googleUser->email)
                    ->documents();

                foreach ($existingUsers as $doc) {
                    if ($doc->exists()) {
                        return redirect('/dashboard')->with('success', 'Welcome back!');
                    }
                }

                session([
                    'google_email' => $googleUser->email,
                    'google_name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'google_avatar' => $googleUser->avatar,
                    'oauth_type' => 'bhw',
                ]);

                return redirect()->route('register.bhw.google');
            }

            $existingUsers = $firestore->collection('rhu')
                ->where('email', '=', $googleUser->email)
                ->documents();

            foreach ($existingUsers as $doc) {
                if ($doc->exists()) {
                    return redirect('/dashboard')->with('success', 'Welcome back!');
                }
            }

            session([
                'google_email' => $googleUser->email,
                'google_name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
                'oauth_type' => 'rhu',
            ]);

            return redirect()->route('register.rhu.google');
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

    public function showGoogleForm()
    {
        if (!session('google_email')) {
            return redirect()->route('register.rhu');
        }
        return view('auth.register_rhu_google');
    }

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
            $uid = Str::uuid();

            $logoUrl = null;
            if ($request->hasFile('logo')) {
                try {
                    $logo = $request->file('logo');
                    \Log::info('Logo file detected (Google OAuth)', [
                        'filename' => $logo->getClientOriginalName(),
                        'size' => $logo->getSize(),
                        'mime' => $logo->getMimeType(),
                        'tmp_path' => $logo->getRealPath(),
                    ]);

                    $cloudinaryUrl = env('CLOUDINARY_URL');
                    \Log::info('Cloudinary environment check (Google OAuth)', [
                        'CLOUDINARY_URL_set' => !empty($cloudinaryUrl),
                        'CLOUDINARY_CLOUD_NAME' => env('CLOUDINARY_CLOUD_NAME'),
                        'CLOUDINARY_API_KEY' => env('CLOUDINARY_API_KEY') ? 'SET' : 'NOT SET',
                    ]);

                    $cloudinary = new Cloudinary();
                    \Log::info('Cloudinary instance created (Google OAuth)');
                    
                    $result = $cloudinary->uploadApi()->upload($logo->getRealPath(), [
                        'folder' => "gabayhealth/rhu/{$uid}",
                        'resource_type' => 'auto',
                        'quality' => 'auto',
                    ]);
                    
                    $logoUrl = $result['secure_url'];
                    \Log::info('Logo uploaded to Cloudinary (Google OAuth)', [
                        'rhu_id' => $uid,
                        'logo_url' => $logoUrl,
                        'public_id' => $result['public_id'] ?? 'N/A',
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Cloudinary upload error (Google OAuth): ' . $e->getMessage(), [
                        'rhu_id' => $uid,
                        'file' => $request->file('logo') ? 'File present' : 'No file',
                        'exception_class' => get_class($e),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                \Log::info('No logo file uploaded for RHU (Google OAuth)', ['rhu_id' => $uid]);
            }

            // Build location object: { latitude, longitude, name } — Google form has no address/coords, use rhuName
            $location = [
                'latitude' => null,
                'longitude' => null,
                'name' => $request->rhuName ?? '',
            ];

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
                'status' => 'pending',
                'logo_url' => $logoUrl,
                'location' => $location,
                'google_id' => session('google_id'),
                'created_at' => now()->toDateTimeString(),
                'username' => null,
                'uid' => null,
            ]);

            \Log::info('RHU registration created (Google OAuth)', [
                'rhu_id' => $uid,
                'rhu_name' => $request->rhuName,
                'logo_url' => $logoUrl,
            ]);
            $this->initializeRhuServicesSubcollection($firestore, $uid);

            $this->saveUserProfile(
                $firestore,
                $uid,
                session('google_email'),
                'rhu',
                $uid,
                $request->rhuName,
                session('google_name', $request->rhuName)
            );

            session()->forget(['google_email', 'google_name', 'google_id', 'google_avatar']);

            return redirect()->route('register.landing')->with('success', 'Registration submitted successfully! Please wait for admin approval and credentials via email.');
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

            $this->saveUserProfile(
                $firestore,
                $uid,
                session('google_email'),
                'barangay',
                $uid,
                $request->healthCenterName,
                session('google_name', $request->healthCenterName)
            );

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

    private function saveUserProfile(
        $firestore,
        string $uid,
        string $email,
        string $role,
        ?string $barangayId,
        ?string $barangayName,
        ?string $fullName = null
    ): void
    {
        $firestore->collection('users')->document($uid)->set([
            'uid' => $uid,
            'email' => $email,
            'role' => $role,
            'barangay_id' => $barangayId ?? '',
            'barangay_name' => $barangayName ?? '',
            'fullname' => $fullName ?? $barangayName ?? '',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], ['merge' => true]);
    }

    private function initializeRhuServicesSubcollection($firestore, string $rhuId): void
    {
        $firestore->collection('rhu')
            ->document($rhuId)
            ->collection('services')
            ->document('_meta')
            ->set([
                '_meta' => true,
                'initialized_at' => now()->toDateTimeString(),
            ], ['merge' => true]);
    }
}