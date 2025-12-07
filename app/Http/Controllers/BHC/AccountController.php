<?php

namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    use HasRoleContext;

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

        return $this->view('accounts.index', compact('healthCenter', 'staffAccounts'));
    }

    // Show health center profile edit form
    public function editProfile()
    {
        $user = session('user');
        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);
        
        return $this->view('accounts.profile', compact('healthCenter'));
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

            return redirect()->route('bhc.accounts.index')->with('success', 'Health center profile updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->withInput();
        }
    }

    // Show create staff account form
    public function createStaff()
    {
        return $this->view('accounts.create-staff');
    }

    // Store new staff account
    public function storeStaff(Request $request)
    {
        $user = session('user');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:doctor,midwife,nurse',
            'contact_number' => 'required|string|max:20',
            'password' => 'required|string|min:6',
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

        try {
            // Prepare staff data
            $staffData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'contact_number' => $validated['contact_number'],
                'password' => Hash::make($validated['password']),
                'specialization' => $validated['specialization'] ?? '',
                'status' => 'active',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            // Save as subcollection under the barangay document
            $documentRef = $this->firestore->getFirestore()
                ->collection($user['role']) // barangay, rhu, or admin
                ->document($user['id'])
                ->collection('accounts')
                ->add($staffData);

            return redirect()->route('bhc.accounts.index')->with('success', 'Staff account created successfully!');
        } catch (\Exception $e) {
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

        return $this->view('accounts.edit-staff', compact('staff'));
    }

    // Update staff account
    public function updateStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:doctor,midwife,nurse',
            'contact_number' => 'required|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $user = session('user');
        $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->document($id)
            ->update([
                ['path' => 'name', 'value' => $validated['name']],
                ['path' => 'email', 'value' => $validated['email']],
                ['path' => 'role', 'value' => $validated['role']],
                ['path' => 'contact_number', 'value' => $validated['contact_number']],
                ['path' => 'specialization', 'value' => $validated['specialization'] ?? ''],
                ['path' => 'status', 'value' => $validated['status']],
                ['path' => 'updated_at', 'value' => now()->toDateTimeString()],
            ]);

        return redirect()->route('bhc.accounts.index')->with('success', 'Staff account updated successfully!');
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

        return redirect()->route('bhc.accounts.index')->with('success', 'Staff account deleted successfully!');
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

        return redirect()->route('bhc.accounts.index')->with('success', 'Password changed successfully!');
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