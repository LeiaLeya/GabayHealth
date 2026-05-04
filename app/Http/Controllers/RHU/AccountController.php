<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;

class AccountController extends Controller
{
    use HasRoleContext;

    protected $firestore;

    public function __construct(FirebaseService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index()
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access account management.');
        }

        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);
        $staffAccounts = $this->getStaffAccounts($user['id'], $user['role']);

        return $this->view('accounts.index', compact('healthCenter', 'staffAccounts'));
    }

    public function editProfile()
    {
        $user = session('user');
        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);

        return $this->view('accounts.profile', compact('healthCenter'));
    }

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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if (!is_array($validated['open_days'])) {
            $validated['open_days'] = [$validated['open_days']];
        }

        $collectionName = $this->getCollectionNameByRole($user['role']);
        $updateData = [
            'healthCenterName' => $validated['healthCenterName'],
            'contact_number' => $validated['contact_number'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'open_time' => $validated['open_time'],
            'close_time' => $validated['close_time'],
            'updated_at' => now()->toDateTimeString(),
        ];

        if (!empty($validated['open_days'])) {
            $updateData['open_days'] = implode(',', $validated['open_days']);
        }

        if ($request->hasFile('logo')) {
            try {
                $cloudinary = new Cloudinary();
                $result = $cloudinary->uploadApi()->upload($request->file('logo')->getRealPath(), [
                    'folder' => "gabayhealth/rhu/{$user['id']}/logo",
                    'resource_type' => 'auto',
                    'quality' => 'auto',
                    'overwrite' => true,
                    'public_id' => 'main-logo',
                ]);
                $updateData['logo_url'] = $result['secure_url'];
                $userSession = session('user');
                $userSession['logo_url'] = $result['secure_url'];
                session(['user' => $userSession]);
            } catch (\Exception $e) {
                // logo upload failed, continue without updating it
            }
        }

        $updateData = array_filter($updateData, function ($value) {
            return $value !== null && $value !== '';
        });

        try {
            $firestoreUpdateData = [];
            foreach ($updateData as $key => $value) {
                $firestoreUpdateData[] = ['path' => $key, 'value' => $value];
            }

            $this->firestore->getFirestore()
                ->collection($collectionName)
                ->document($user['id'])
                ->update($firestoreUpdateData);

            return redirect()->route('rhu.accounts.index')->with('success', 'Health center profile updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->withInput();
        }
    }

    public function createStaff()
    {
        return $this->view('accounts.create-staff');
    }

    public function storeStaff(Request $request)
    {
        $user = session('user');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:doctor,midwife,nurse,bhw',
            'contact_number' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'specialization' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $existingUser = $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->where('email', '=', $validated['email'])
            ->documents();

        if (iterator_count($existingUser) > 0) {
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }

        $uid = null;
        $auth = $this->firestore->getAuth();

        try {
            $authUser = $auth->createUser([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'displayName' => $validated['name'],
                'emailVerified' => false,
            ]);

            $uid = $authUser->uid;

            $staffData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'contact_number' => $validated['contact_number'],
                'specialization' => $validated['specialization'] ?? '',
                'address' => $validated['address'] ?? '',
                'status' => 'active',
                'uid' => $uid,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            $this->firestore->getFirestore()
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('accounts')
                ->add($staffData);

            $this->firestore->getFirestore()
                ->collection('users')
                ->document($uid)
                ->set([
                    'uid' => $uid,
                    'email' => $validated['email'],
                    'fullname' => $validated['name'],
                    'role' => $validated['role'],
                    'barangay_id' => $user['barangayId'] ?? $user['id'],
                    'barangay_name' => $user['name'] ?? $user['healthCenterName'] ?? '',
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ], ['merge' => true]);

            return redirect()->route('rhu.accounts.index')->with('success', 'Staff account created successfully!');
        } catch (\Kreait\Firebase\Auth\Exception\AuthException $e) {
            $message = 'Failed to create staff account.';
            if (str_contains($e->getMessage(), 'EMAIL_EXISTS')) {
                $message = 'The email address is already registered.';
            }

            return back()->withErrors(['email' => $message])->withInput();
        } catch (\Exception $e) {
            if ($uid) {
                try {
                    $auth->deleteUser($uid);
                } catch (\Throwable $cleanupException) {
                    // cleanup failed, ignore
                }
            }

            return back()->withErrors(['error' => 'Failed to create staff account: ' . $e->getMessage()])->withInput();
        }
    }

    public function editStaff($id)
    {
        $staff = $this->getStaffAccount($id);

        if (!$staff) {
            abort(404);
        }

        return $this->view('accounts.edit-staff', compact('staff'));
    }

    public function updateStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:doctor,midwife,nurse',
            'contact_number' => 'required|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
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
                ['path' => 'address', 'value' => $validated['address'] ?? ''],
                ['path' => 'status', 'value' => $validated['status']],
                ['path' => 'updated_at', 'value' => now()->toDateTimeString()],
            ]);

        return redirect()->route('rhu.accounts.index')->with('success', 'Staff account updated successfully!');
    }

    public function destroyStaff($id)
    {
        $user = session('user');
        $this->firestore->getFirestore()
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('accounts')
            ->document($id)
            ->delete();

        return redirect()->route('rhu.accounts.index')->with('success', 'Staff account deleted successfully!');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = session('user');
        $healthCenter = $this->getHealthCenterProfile($user['id'], $user['role']);

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

        return redirect()->route('rhu.accounts.index')->with('success', 'Password changed successfully!');
    }

    public function uploadLogo(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $user = session('user');

        try {
            $logo = $request->file('logo');
            $cloudinary = new Cloudinary();

            $result = $cloudinary->uploadApi()->upload($logo->getRealPath(), [
                'folder' => "gabayhealth/rhu/{$user['id']}/logo",
                'resource_type' => 'auto',
                'quality' => 'auto',
                'overwrite' => true,
                'public_id' => 'main-logo',
            ]);

            $logoUrl = $result['secure_url'];

            $collectionName = $this->getCollectionNameByRole($user['role']);
            $this->firestore->getFirestore()
                ->collection($collectionName)
                ->document($user['id'])
                ->update([
                    ['path' => 'logo_url', 'value' => $logoUrl],
                    ['path' => 'updated_at', 'value' => now()->toDateTimeString()],
                ]);

            $userSession = session('user');
            $userSession['logo_url'] = $logoUrl;
            session(['user' => $userSession]);

            return back()->with('success', 'Health center logo updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to upload logo: ' . $e->getMessage()]);
        }
    }

    public function deleteLogo(Request $request)
    {
        $user = session('user');

        try {
            $cloudinary = new Cloudinary();

            try {
                $cloudinary->uploadApi()->destroy("gabayhealth/rhu/{$user['id']}/logo/main-logo", [
                    'resource_type' => 'image',
                ]);
            } catch (\Exception $e) {
                // Cloudinary deletion failed, continue anyway
            }

            $collectionName = $this->getCollectionNameByRole($user['role']);
            $this->firestore->getFirestore()
                ->collection($collectionName)
                ->document($user['id'])
                ->update([
                    ['path' => 'logo_url', 'value' => null],
                    ['path' => 'updated_at', 'value' => now()->toDateTimeString()],
                ]);

            $userSession = session('user');
            $userSession['logo_url'] = null;
            session(['user' => $userSession]);

            return back()->with('success', 'Health center logo deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete logo: ' . $e->getMessage()]);
        }
    }

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
                $staffAccounts[] = array_merge(['id' => $document->id()], $document->data());
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
