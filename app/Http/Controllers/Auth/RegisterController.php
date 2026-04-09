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
    public function landing()
    {
        return view('auth.register_landing');
    }

    public function showBhwForm()
    {
        $firestore = app(\App\Services\FirebaseService::class)->getFirestore();
        $rhuDocs = $firestore->collection('rhu')->where('status', '=', 'active')->documents();
        $rhus = [];
        foreach ($rhuDocs as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $rhus[] = [
                    'id' => $doc->id(),
                    'name' => $data['rhuName'] ?? $data['name'] ?? 'Unnamed RHU',
                ];
            }
        }
        \Log::info('Active RHUs for BHW registration:', $rhus);
        return view('auth.register_bhw', compact('rhus'));
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
                'created_at' => now()->toDateTimeString(),
            ]);

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

                    $cloudinaryUrl = env('CLOUDINARY_URL');
                    \Log::info('Cloudinary environment check', [
                        'CLOUDINARY_URL_set' => !empty($cloudinaryUrl),
                        'CLOUDINARY_CLOUD_NAME' => env('CLOUDINARY_CLOUD_NAME'),
                        'CLOUDINARY_API_KEY' => env('CLOUDINARY_API_KEY') ? 'SET' : 'NOT SET',
                    ]);

                    $cloudinary = new Cloudinary();
                    \Log::info('Cloudinary instance created');
                    
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
                'created_at' => now()->toDateTimeString(),
                'username' => null,
                'uid' => null,
            ]);

            \Log::info('RHU registration created', [
                'rhu_id' => $uid,
                'rhu_name' => $request->rhuName,
                'logo_url' => $logoUrl,
            ]);

            return back()->with('success', 'RHU registration submitted successfully! Please wait for admin approval and credentials via email.');
        } catch (\Exception $e) {
            \Log::error('Error during RHU registration: ' . $e->getMessage());
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
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
            ]);

            return redirect()->route('register.rhu.google');
        } catch (Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
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

            session()->forget(['google_email', 'google_name', 'google_id', 'google_avatar']);

            return redirect()->route('register.landing')->with('success', 'Registration submitted successfully! Please wait for admin approval and credentials via email.');
        } catch (Exception $e) {
            \Log::error('Google RHU Registration error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}