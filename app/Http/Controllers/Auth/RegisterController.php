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

        $firestore = app(FirebaseService::class)->getFirestore();
        $docRef = $firestore->collection('barangay')->add([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'healthCenterName' => $request->healthCenterName,
            'fullAddress' => $request->fullAddress,
            'region' => $request->region,
            'province' => $request->province,
            'city' => $request->city,
            'barangay' => $request->barangay,
            'postalCode' => $request->postalCode,
            'rhuId' => $request->rhuId,
            'status' => 'pending',
            'created_at' => now()->toDateTimeString(),
        ]);

        // Notify the selected RHU (add a notification document)
        $firestore->collection('rhu')->document($request->rhuId)
            ->collection('notifications')->add([
                'type' => 'barangay_registration',
                'barangay_id' => $docRef->id(),
                'barangay_name' => $request->healthCenterName,
                'created_at' => now()->toDateTimeString(),
                'status' => 'unread',
            ]);

        return back()->with('success', 'Barangay registration submitted! Waiting for RHU approval.');
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

        $firestore = app(FirebaseService::class)->getFirestore();
        $docRef = $firestore->collection('rhu')->add([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'name' => $request->rhuName,
            'fullAddress' => $request->fullAddress,
            'region' => $request->region,
            'province' => $request->province,
            'city' => $request->city,
            'status' => 'pending',
            'created_at' => now()->toDateTimeString(),
        ]);

        // Optionally, notify admin (could add to an 'admin_notifications' collection)
        // $firestore->collection('admin_notifications')->add([...]);

        return back()->with('success', 'RHU registration submitted! Waiting for admin approval.');
    }
} 