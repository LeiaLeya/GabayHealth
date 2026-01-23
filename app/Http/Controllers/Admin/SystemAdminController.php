<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Str;
use Exception;

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

            return view('admin.system-admin.view-application', compact('rhu'));
        } catch (Exception $e) {
            \Log::error('Error viewing RHU application: ' . $e->getMessage());
            return back()->with('error', 'Failed to load RHU details.');
        }
    }

    /**
     * Approve RHU and generate credentials
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

            // Generate credentials
            $username = 'RHU_' . strtoupper(substr(Str::uuid(), 0, 8));
            $tempPassword = $this->generateSecurePassword();

            // Create Firebase Auth user
            try {
                $authUser = $this->auth->createUser([
                    'email' => $rhuEmail,
                    'password' => $tempPassword,
                    'displayName' => $rhuName,
                    'emailVerified' => false,
                ]);

                $uid = $authUser->uid;

                // Update Firestore document with credentials and approved status
                $this->firestore->collection('rhu')->document($rhuId)->update([
                    ['path' => 'username', 'value' => $username],
                    ['path' => 'uid', 'value' => $uid],
                    ['path' => 'status', 'value' => 'credentials_sent'],
                    ['path' => 'credentials_generated_at', 'value' => now()->toDateTimeString()],
                    ['path' => 'credentials_sent_at', 'value' => now()->toDateTimeString()],
                    ['path' => 'approved_by', 'value' => $user['id']],
                    ['path' => 'temp_password', 'value' => $tempPassword], // Store for reference
                ]);

                // Send email with credentials
                $this->sendCredentialsEmail($rhuEmail, $username, $tempPassword, $rhuName);

                return response()->json([
                    'success' => true,
                    'message' => 'RHU approved and credentials sent successfully.',
                    'username' => $username,
                    'email' => $rhuEmail,
                ]);
            } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
                \Log::error('Firebase Auth: Email already exists - ' . $e->getMessage());
                return response()->json(['error' => 'Email already registered in system'], 422);
            }
        } catch (Exception $e) {
            \Log::error('Error approving RHU: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve RHU'], 500);
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
     * Generate a secure password
     */
    private function generateSecurePassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';

        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];

        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Send credentials email to RHU
     */
    private function sendCredentialsEmail($email, $username, $tempPassword, $rhuName)
    {
        // TODO: Implement proper email sending
        // For now, we'll just log it
        \Log::info("Credentials sent to {$email}", [
            'username' => $username,
            'rhuName' => $rhuName,
        ]);

        // This is where you'd use Mail::send() or a mailable class
        // Example:
        // Mail::to($email)->send(new RhuCredentialsEmail($username, $tempPassword, $email));
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
}
