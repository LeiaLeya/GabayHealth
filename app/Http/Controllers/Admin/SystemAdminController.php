<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Http;

class SystemAdminController extends Controller
{
    protected $firestore;
    protected $auth;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firestore = $firebaseService->getFirestore();
        $this->auth = $firebaseService->getAuth();
    }

    /**
     * System Admin Dashboard - Pending RHU Applications
     */
    public function dashboard()
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        try {
            // Get pending RHU applications
            $pendingRhus = [];
            $rhuDocs = $this->firestore->collection('rhu')
                ->where('status', '=', 'pending')
                ->documents();

            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $pendingRhus[] = array_merge(['id' => $doc->id()], $data);
                }
            }

            // Get statistics
            $stats = [
                'pending' => count($pendingRhus),
                'approved' => $this->countByStatus('approved'),
                'active' => $this->countByStatus('active'),
                'rejected' => $this->countByStatus('rejected'),
            ];

            return view('admin.system-admin.dashboard', compact('pendingRhus', 'stats'));
        } catch (Exception $e) {
            \Log::error('Error loading System Admin dashboard: ' . $e->getMessage());
            return back()->with('error', 'Failed to load dashboard.');
        }
    }

    /**
     * View RHU application details
     */
    public function viewApplication($rhuId)
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        try {
            $rhuDoc = $this->firestore->collection('rhu')->document($rhuId)->snapshot();

            if (!$rhuDoc->exists()) {
                return back()->with('error', 'RHU not found.');
            }

            $rhu = array_merge(['id' => $rhuId], $rhuDoc->data());
            
            // Resolve location names from PSGC codes
            if (isset($rhu['region']) && isset($rhu['province']) && isset($rhu['city'])) {
                $location = $this->getLocationFromPSGC($rhu['region'], $rhu['province'], $rhu['city']);
                if ($location) {
                    $rhu['displayLocation'] = $location;
                }
            }

            return view('admin.system-admin.view-application', compact('rhu'));
        } catch (Exception $e) {
            \Log::error('Error viewing RHU application: ' . $e->getMessage());
            return back()->with('error', 'Failed to load RHU details.');
        }
    }

    /**
     * Approve RHU and send account setup email
     */
    public function approveAndSendCredentials($rhuId)
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $rhuDoc = $this->firestore->collection('rhu')->document($rhuId)->snapshot();

            if (!$rhuDoc->exists()) {
                return response()->json(['error' => 'RHU not found'], 404);
            }

            $rhuData = $rhuDoc->data();
            $rhuEmail = $rhuData['email'];
            $rhuName = $rhuData['rhuName'] ?? $rhuData['name'];

            // Generate username for the RHU
            $username = 'RHU_' . strtoupper(substr(Str::uuid(), 0, 8));

            // Try to get or create Firebase Auth user
            $uid = null;
            try {
                // Try to create Firebase Auth user
                $authUser = $this->auth->createUser([
                    'email' => $rhuEmail,
                    'displayName' => $rhuName,
                    'emailVerified' => false,
                ]);
                $uid = $authUser->uid;
            } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
                // Email already exists, try to get the existing user
                \Log::info('Firebase user already exists for email: ' . $rhuEmail);
                try {
                    $existingUser = $this->auth->getUserByEmail($rhuEmail);
                    $uid = $existingUser->uid;
                } catch (Exception $getUserException) {
                    \Log::error('Could not get existing Firebase user: ' . $getUserException->getMessage());
                    throw new Exception('Email already registered in system');
                }
            }

            // Update Firestore document with username and UID, status to pending_setup
            $this->firestore->collection('rhu')->document($rhuId)->update([
                ['path' => 'username', 'value' => $username],
                ['path' => 'uid', 'value' => $uid],
                ['path' => 'status', 'value' => 'pending_setup'],
                ['path' => 'approved_by', 'value' => $user['id']],
                ['path' => 'approved_at', 'value' => now()->toDateTimeString()],
            ]);

            // Send setup email with token
            $setupController = new \App\Http\Controllers\Auth\RhuAccountSetupController();
            $emailSent = $setupController::sendSetupEmail($rhuId, $rhuEmail, $rhuName, $username);

            if (!$emailSent) {
                throw new Exception('Failed to send setup email');
            }

            \Log::info('RHU approved and setup email sent', [
                'rhu_id' => $rhuId,
                'username' => $username,
                'email' => $rhuEmail,
                'approved_by' => $user['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RHU approved! Setup email has been sent to ' . $rhuEmail,
                'username' => $username,
                'email' => $rhuEmail,
            ]);
        } catch (Exception $e) {
            \Log::error('Error approving RHU: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve RHU: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject RHU application
     */
    public function rejectApplication(Request $request, $rhuId)
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $rhuDoc = $this->firestore->collection('rhu')->document($rhuId)->snapshot();

            if (!$rhuDoc->exists()) {
                return response()->json(['error' => 'RHU not found'], 404);
            }

            // Update status to rejected
            $this->firestore->collection('rhu')->document($rhuId)->update([
                ['path' => 'status', 'value' => 'rejected'],
                ['path' => 'rejection_reason', 'value' => $request->reason],
                ['path' => 'rejected_by', 'value' => $user['id']],
                ['path' => 'rejected_at', 'value' => now()->toDateTimeString()],
            ]);

            // TODO: Send rejection email to RHU

            return response()->json([
                'success' => true,
                'message' => 'RHU application rejected.',
            ]);
        } catch (Exception $e) {
            \Log::error('Error rejecting RHU: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reject RHU'], 500);
        }
    }

    /**
     * View approved RHUs
     */
    public function approvedRhus()
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        try {
            $approvedRhus = [];
            $rhuDocs = $this->firestore->collection('rhu')
                ->where('status', '=', 'credentials_sent')
                ->documents();

            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $approvedRhus[] = array_merge(['id' => $doc->id()], $data);
                }
            }

            return view('admin.system-admin.approved-rhus', compact('approvedRhus'));
        } catch (Exception $e) {
            \Log::error('Error loading approved RHUs: ' . $e->getMessage());
            return back()->with('error', 'Failed to load approved RHUs.');
        }
    }

    /**
     * View all RHUs
     */
    public function allRhus()
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        try {
            $rhus = [];
            $rhuDocs = $this->firestore->collection('rhu')->documents();

            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    // Resolve location names from PSGC codes
                    if (isset($data['region']) && isset($data['province']) && isset($data['city'])) {
                        $location = $this->getLocationFromPSGC($data['region'], $data['province'], $data['city']);
                        if ($location) {
                            $data['displayLocation'] = $location;
                        }
                    }
                    $rhus[] = array_merge(['id' => $doc->id()], $data);
                }
            }

            // Sort by status and created date
            usort($rhus, function ($a, $b) {
                $statusOrder = ['pending' => 1, 'credentials_sent' => 2, 'active' => 3, 'rejected' => 4];
                $aStatus = $statusOrder[$a['status'] ?? 'pending'] ?? 5;
                $bStatus = $statusOrder[$b['status'] ?? 'pending'] ?? 5;
                
                if ($aStatus !== $bStatus) {
                    return $aStatus <=> $bStatus;
                }
                
                return ($b['created_at'] ?? '') <=> ($a['created_at'] ?? '');
            });

            return view('admin.system-admin.all-rhus', compact('rhus'));
        } catch (Exception $e) {
            \Log::error('Error loading all RHUs: ' . $e->getMessage());
            return back()->with('error', 'Failed to load RHUs.');
        }
    }

    /**
     * Resend credentials to an RHU
     */
    public function resendCredentials($rhuId)
    {
        $user = session('user');
        
        if (!$user || $user['role'] !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $rhuDoc = $this->firestore->collection('rhu')->document($rhuId)->snapshot();

            if (!$rhuDoc->exists()) {
                return response()->json(['error' => 'RHU not found'], 404);
            }

            $rhuData = $rhuDoc->data();
            $username = $rhuData['username'];
            $tempPassword = $rhuData['temp_password'];

            if (!$username || !$tempPassword) {
                return response()->json(['error' => 'Credentials not found for this RHU'], 422);
            }

            $rhuEmail = $rhuData['email'];
            $rhuName = $rhuData['rhuName'] ?? $rhuData['name'];

            // Send email
            $this->sendCredentialsEmail($rhuEmail, $username, $tempPassword, $rhuName);

            // Update last sent time
            $this->firestore->collection('rhu')->document($rhuId)->update([
                ['path' => 'credentials_resent_at', 'value' => now()->toDateTimeString()],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credentials resent successfully.',
            ]);
        } catch (Exception $e) {
            \Log::error('Error resending credentials: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to resend credentials'], 500);
        }
    }



    /**
     * Count RHUs by status
     */
    private function countByStatus($status)
    {
        try {
            $count = 0;
            $rhuDocs = $this->firestore->collection('rhu')
                ->where('status', '=', $status)
                ->documents();

            foreach ($rhuDocs as $doc) {
                if ($doc->exists()) {
                    $count++;
                }
            }

            return $count;
        } catch (Exception $e) {
            \Log::error('Error counting RHUs by status: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper to get location name with province and region from PSGC codes
     */
    private function getLocationFromPSGC($regionCode, $provinceCode, $cityCode)
    {
        try {
            $locationParts = [];
            
            // Query the city/municipality
            $cityResponse = Http::get("https://psgc.gitlab.io/api/cities/{$cityCode}.json");
            if ($cityResponse->successful()) {
                $cityData = $cityResponse->json();
                $locationParts[] = $cityData['name'] ?? '';
            }
            
            // Get province name
            if ($provinceCode) {
                try {
                    $provinceResponse = Http::get("https://psgc.gitlab.io/api/provinces/{$provinceCode}.json");
                    if ($provinceResponse->successful()) {
                        $provinceData = $provinceResponse->json();
                        $locationParts[] = $provinceData['name'] ?? '';
                    }
                } catch (\Exception $e) {
                    // Continue without province
                }
            }
            
            // Get region name
            if ($regionCode) {
                try {
                    $regionResponse = Http::get("https://psgc.gitlab.io/api/regions/{$regionCode}.json");
                    if ($regionResponse->successful()) {
                        $regionData = $regionResponse->json();
                        $locationParts[] = $regionData['name'] ?? '';
                    }
                } catch (\Exception $e) {
                    // Continue without region
                }
            }
            
            return implode(', ', array_filter($locationParts)) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
