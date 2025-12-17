<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Auth\Exception\AuthException;

class AccountController extends Controller
{
    protected $firestore;

    public function __construct(FirebaseService $firestore)
    {
        $this->firestore = $firestore;
    }

    // Show account management dashboard
    public function index()
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access account management.');
        }

        // Get health center profile based on user role
        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);
        
        // Get staff accounts for this health center
        $staffAccounts = $this->getStaffAccounts($user['id'], $user['role']);

        return view('pages.accounts.index', compact('healthCenter', 'staffAccounts'));
    }

    // Show health center profile edit form
    public function editProfile()
    {
        $user = session('user');
        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);
        
        return view('pages.accounts.profile', compact('healthCenter'));
    }

    // Update health center profile
    public function updateProfile(Request $request)
    {
        $user = session('user');
        
        $validated = $request->validate([
            'healthCenterName' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'open_days' => 'required|array|min:1',
            'open_time' => 'required|string|max:10',
            'close_time' => 'required|string|max:10',
        ]);

        // Ensure open_days is always an array
        if (!is_array($validated['open_days'])) {
            $validated['open_days'] = [$validated['open_days']];
        }

        $collectionName = $this->getCollectionNameByRole($user['role']);
        // Prepare update data
        $updateData = [
            'healthCenterName' => $validated['healthCenterName'],
            'contact_number' => $validated['contact_number'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'open_time' => $validated['open_time'],
            'close_time' => $validated['close_time'],
            'updated_at' => now()->toDateTimeString(),
        ];

        // Handle open_days separately to ensure it's properly formatted
        if (!empty($validated['open_days'])) {
            // Convert array to string for Firestore compatibility
            $updateData['open_days'] = implode(',', $validated['open_days']);
        }

        // Remove any empty values to avoid Firestore errors
        $updateData = array_filter($updateData, function($value) {
            return $value !== null && $value !== '';
        });

        try {
            // Convert array to Firestore update format
            $firestoreUpdateData = [];
            foreach ($updateData as $key => $value) {
                $firestoreUpdateData[] = ['path' => $key, 'value' => $value];
            }

            // Debug: Log the data being sent
            \Log::info('Firestore update data:', $firestoreUpdateData);

            $this->firestore->getFirestore()
                ->collection($collectionName)
                ->document($user['id'])
                ->update($firestoreUpdateData);

            return redirect()->route('accounts.index')->with('success', 'Health center profile updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->withInput();
        }
    }

    // Show create staff account form
    public function createStaff()
    {
        return view('pages.accounts.create-staff');
    }

    // Store new staff account
    public function storeStaff(Request $request)
    {
        $user = session('user');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:doctor,midwife,nurse,bhw',
            'contact_number' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'specialization' => 'nullable|string|max:255',
        ]);

        // Check if email already exists in the accounts subcollection
        $existingUser = $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->where('email', '=', $validated['email'])
            ->documents();

        if (iterator_count($existingUser) > 0) {
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }

        // Check if contact number already exists in the accounts subcollection
        $existingPhone = $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->where('contact_number', '=', $validated['contact_number'])
            ->documents();

        if (iterator_count($existingPhone) > 0) {
            return back()->withErrors(['contact_number' => 'Contact number already exists.'])->withInput();
        }

        $uid = null;
        $auth = $this->firestore->getAuth();

        try {
            $phoneNumber = $validated['contact_number'];
            if (!empty($phoneNumber) && !str_starts_with($phoneNumber, '+')) {
                $phoneNumber = preg_replace('/^0/', '', $phoneNumber);
                if (!str_starts_with($phoneNumber, '+63')) {
                    $phoneNumber = '+63' . $phoneNumber;
                }
            }
            
            // Create Firebase Auth user
            $createUserData = [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'displayName' => $validated['name'],
                'emailVerified' => false,
            ];
            
            // Only add phoneNumber if it's properly formatted (starts with +)
            if (!empty($phoneNumber) && str_starts_with($phoneNumber, '+')) {
                $createUserData['phoneNumber'] = $phoneNumber;
            }
            
            $authUser = $auth->createUser($createUserData);

            $uid = $authUser->uid;

            // Map role for Firebase (bhw -> bhw, nurse -> nurse, doctor -> doctor, midwife -> midwife)
            $firebaseRole = $validated['role']; // Already in correct format

            // Prepare staff data
            $staffData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $firebaseRole,
                'contact_number' => $validated['contact_number'],
                'password' => Hash::make($validated['password']), // Keep for backward compatibility
                'specialization' => $validated['specialization'] ?? '',
                'status' => 'active',
                'uid' => $uid, // Store Firebase UID
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            // Save as subcollection under the health center document
            $documentRef = $this->firestore->getFirestore()
                ->collection($user['role']) // barangay, rhu, or admin
                ->document($user['id'])
                ->collection('accounts')
                ->add($staffData);

            return redirect()->route('accounts.index')->with('success', 'Staff account created successfully!');
        } catch (\Kreait\Firebase\Auth\Exception\AuthException $e) {
            // If Firebase Auth creation fails, clean up if UID was created
            if ($uid) {
                try {
                    $auth->deleteUser($uid);
                } catch (\Exception $cleanupException) {
                    \Log::warning('Failed to cleanup auth user after error: ' . $cleanupException->getMessage());
                }
            }

            $errorMessage = 'Failed to create staff account.';
            if (str_contains($e->getMessage(), 'EMAIL_EXISTS')) {
                $errorMessage = 'The email address is already registered in Firebase Auth.';
            }

            return back()->withErrors(['error' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            // If Firestore save fails but Auth user was created, try to clean up
            if ($uid) {
                try {
                    $auth->deleteUser($uid);
                } catch (\Exception $cleanupException) {
                    \Log::warning('Failed to cleanup auth user after Firestore error: ' . $cleanupException->getMessage());
                }
            }

            return back()->withErrors(['error' => 'Failed to create staff account: ' . $e->getMessage()])->withInput();
        }
    }

    // Show edit staff account form
    public function editStaff($id)
    {
        $staff = $this->getStaffAccount($id);
        
        if (!$staff) {
            abort(404);
        }

        return view('pages.accounts.edit-staff', compact('staff'));
    }

    // Update staff account
    public function updateStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:doctor,midwife,nurse,bhw',
            'contact_number' => 'required|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $user = session('user');
        
        // Get existing staff to check for UID
        $staff = $this->getStaffAccount($id);
        $uid = $staff['uid'] ?? null;
        
        // Check if contact number already exists (excluding current staff)
        if (($staff['contact_number'] ?? '') !== $validated['contact_number']) {
            $existingPhone = $this->firestore->getFirestore()
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('accounts')
                ->where('contact_number', '=', $validated['contact_number'])
                ->documents();

            if (iterator_count($existingPhone) > 0) {
                return back()->withErrors(['contact_number' => 'Contact number already exists.'])->withInput();
            }
        }
        
        // Map role for Firebase
        $firebaseRole = $validated['role']; // Already in correct format
        
        // Update Firestore
        $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->document($id)
            ->update([
                ['path' => 'name', 'value' => $validated['name']],
                ['path' => 'email', 'value' => $validated['email']],
                ['path' => 'role', 'value' => $firebaseRole],
                ['path' => 'contact_number', 'value' => $validated['contact_number']],
                ['path' => 'specialization', 'value' => $validated['specialization'] ?? ''],
                ['path' => 'status', 'value' => $validated['status']],
                ['path' => 'updated_at', 'value' => now()->toDateTimeString()],
            ]);

        // Update Firebase Auth user if UID exists
        if ($uid) {
            try {
                $auth = $this->firestore->getAuth();
                
                // Format phone number for Firebase Auth (E.164 format: +country code + number)
                // If phone doesn't start with +, try to format it (assuming Philippines +63)
                $phoneNumber = $validated['contact_number'];
                if (!empty($phoneNumber) && !str_starts_with($phoneNumber, '+')) {
                    // Remove leading 0 if present and add +63 for Philippines
                    $phoneNumber = preg_replace('/^0/', '', $phoneNumber);
                    if (!str_starts_with($phoneNumber, '+63')) {
                        $phoneNumber = '+63' . $phoneNumber;
                    }
                }
                
                $updateData = [
                    'email' => $validated['email'],
                    'displayName' => $validated['name'],
                ];
                
                // Only add phoneNumber if it's not empty and properly formatted
                if (!empty($phoneNumber) && str_starts_with($phoneNumber, '+')) {
                    $updateData['phoneNumber'] = $phoneNumber;
                }
                
                $auth->updateUser($uid, $updateData);
            } catch (\Exception $e) {
                \Log::warning('Failed to update Firebase Auth user: ' . $e->getMessage());
                // Continue even if Auth update fails
            }
        }

        return redirect()->route('accounts.index')->with('success', 'Staff account updated successfully!');
    }

    // Delete staff account
    public function destroyStaff($id)
    {
        $user = session('user');
        $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->document($id)
            ->delete();

        return redirect()->route('accounts.index')->with('success', 'Staff account deleted successfully!');
    }

    // Change password for current user
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = session('user');
        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);

        // Verify current password
        if (!Hash::check($validated['current_password'], $healthCenter['password'] ?? '')) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $collectionName = $this->getCollectionNameByRole($user['role']);
        $this->firestore->getFirestore()
            ->collection($collectionName)
            ->document($user['id'])
            ->update([
                ['path' => 'password', 'value' => Hash::make($validated['new_password'])],
                ['path' => 'updated_at', 'value' => now()->toDateTimeString()],
            ]);

        return redirect()->route('accounts.index')->with('success', 'Password changed successfully!');
    }

    // Helper methods
    private function getHealthCenterProfile($userId, $userRole)
    {
        $collectionName = $this->getCollectionNameByRole($userRole);
        $document = $this->firestore->getFirestore()
            ->collection($collectionName)
            ->document($userId)
            ->snapshot();

        if (!$document->exists()) {
            return null;
        }

        return array_merge(['id' => $document->id()], $document->data());
    }

    private function getStaffAccounts($userId, $userRole)
    {
        $documents = $this->firestore->getFirestore()
            ->collection($userRole)
            ->document($userId)
            ->collection('accounts')
            ->documents();

        $staffAccounts = [];
        foreach ($documents as $document) {
            if ($document->exists()) {
                $data = $document->data();
                $staffAccounts[] = array_merge(['id' => $document->id()], $data);
            }
        }

        return $staffAccounts;
    }

    private function getCollectionNameByRole($role)
    {
        switch ($role) {
            case 'barangay':
                return 'barangay';
            case 'rhu':
                return 'rhu';
            case 'admin':
                return 'admin';
            default:
                return 'barangay';
        }
    }

    private function getStaffAccount($id)
    {
        $user = session('user');
        
        // We need to search through the accounts subcollection
        $documents = $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->documents();

        foreach ($documents as $document) {
            if ($document->id() === $id && $document->exists()) {
                return array_merge(['id' => $document->id()], $document->data());
            }
        }

        return null;
    }
} 