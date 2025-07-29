<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Hash;

class RHURegistrationController extends Controller
{
    public function create()
    {
        return view('auth.registerRHU');
    }

    public function store(Request $request, FirestoreService $firestore)
    {
        $validated = $request->validate([
            'loginField' => 'required|string|max:255',
            'contactNumber' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'rhuName' => 'required|string|max:255',
            'headName' => 'required|string|max:255',
            'licenseNumber' => 'nullable|string|max:100',
            'operatingHours' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'fullAddress' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'zipCode' => 'nullable|string|max:10',
        ]);

        $existingAdmin = $firestore->db->collection('admin')->where('loginField', '=', $validated['loginField'])->documents();
        if (iterator_count($existingAdmin) > 0) {
            return back()->withErrors(['loginField' => 'This login credential is already taken.'])->withInput();
        }

        $existingRHU = $firestore->db->collection('rhu')->where('loginField', '=', $validated['loginField'])->documents();
        if (iterator_count($existingRHU) > 0) {
            return back()->withErrors(['loginField' => 'This login credential is already taken.'])->withInput();
        }

        $existingContact = $firestore->db->collection('rhu')->where('contactNumber', '=', $validated['contactNumber'])->documents();
        if (iterator_count($existingContact) > 0) {
            return back()->withErrors(['contactNumber' => 'This mobile number is already registered.'])->withInput();
        }

        try {
            // Store everything in rhu collection
            $rhuData = [
                'loginField' => $validated['loginField'],
                'contactNumber' => $validated['contactNumber'],
                'password' => Hash::make($validated['password']),
                'name' => $validated['rhuName'],
                'headName' => $validated['headName'],
                'licenseNumber' => $validated['licenseNumber'] ?? '',
                'operatingHours' => $validated['operatingHours'],
                'description' => $validated['description'] ?? '',
                'fullAddress' => $validated['fullAddress'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'region' => $validated['region'],
                'zipCode' => $validated['zipCode'] ?? '',
                'status' => 'pending',
                'createdAt' => now()->toDateTimeString(),
                'updatedAt' => now()->toDateTimeString(),
                'approvedBy' => null,
                'approvedAt' => null,
            ];

            $firestore->addDocument('rhu', $rhuData);

            return redirect()->route('login')->with('success', 'RHU registration submitted successfully! Please wait for admin approval.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}