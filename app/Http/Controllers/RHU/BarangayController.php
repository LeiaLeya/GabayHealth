<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class BarangayController extends Controller
{
    use HasRoleContext;

    protected $firestore;
    protected $auth;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        $this->auth = $firebase->getAuth();
    }

    /**
     * Display all barangays registered under this RHU
     */
    public function index()
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view barangays.');
        }

        // Get RHU ID from session
        $rhuId = $this->getBarangayId();
        
        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $barangays = [];
        
        try {
            // Fetch all barangays under this RHU (remove status filter to see all)
            $barangayDocs = $this->firestore
                ->collection('barangay')
                ->where('rhuId', '=', $rhuId)
                ->documents();

            foreach ($barangayDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    
                    // Extract barangay name from location.name
                    $barangayName = 'Unknown Barangay';
                    $location = 'Unknown Location';
                    if (!empty($data['location']['name'])) {
                        // Extract first part before comma (e.g., "Guindaruhan" from "Guindaruhan, Minglanilla, Cebu, Philippines")
                        $locationParts = explode(',', $data['location']['name']);
                        $barangayName = trim($locationParts[0]);
                        $location = trim($data['location']['name']);
                    }
                    
                    // Get created or approved date
                    $appliedDate = $data['createdAt'] ?? $data['created_at'] ?? $data['approved_at'] ?? null;
                    if ($appliedDate && is_string($appliedDate)) {
                        try {
                            $appliedDate = \Carbon\Carbon::parse($appliedDate)->format('M d, Y');
                        } catch (\Exception $e) {
                            $appliedDate = 'N/A';
                        }
                    } else {
                        $appliedDate = 'N/A';
                    }
                    
                    $barangays[] = [
                        'id' => $doc->id(),
                        'barangayName' => $barangayName,
                        'healthCenterName' => $data['healthCenterName'] ?? 'Health Center',
                        'email' => $data['email'] ?? 'N/A',
                        'logo_url' => $data['logo_url'] ?? null,
                        'status' => $data['status'] ?? 'unknown',
                        'address' => $data['address'] ?? $location,
                        'location' => $location,
                        'appliedDate' => $appliedDate,
                    ];
                }
            }
            \Log::info('Total barangays fetched: ' . count($barangays));

            // Sort by BHC name
            usort($barangays, function($a, $b) {
                return strcmp($a['healthCenterName'], $b['healthCenterName']);
            });

        } catch (\Exception $e) {
            \Log::error('Error fetching barangays under RHU: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load barangays.');
        }

        return view('rhu.barangays.index', compact('barangays'));
    }

    /**
     * Display details of a single barangay
     */
    public function show($barangayId)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view barangay details.');
        }

        // Verify user is from an RHU
        $rhuId = $this->getBarangayId();
        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        try {
            $barangayDoc = $this->firestore
                ->collection('barangay')
                ->document($barangayId)
                ->snapshot();

            if (!$barangayDoc->exists()) {
                return redirect()->route('rhu.barangays.index')->with('error', 'Barangay not found.');
            }

            $barangayData = $barangayDoc->data();

            // Verify this barangay belongs to the user's RHU
            if (($barangayData['rhuId'] ?? '') !== $rhuId) {
                return redirect()->route('rhu.barangays.index')->with('error', 'You do not have permission to view this barangay.');
            }

            // Extract barangay name from location.name
            $barangayName = 'Unknown Barangay';
            if (!empty($barangayData['location']) && is_array($barangayData['location']) && !empty($barangayData['location']['name'])) {
                // Extract first part before comma (e.g., "Guindaruhan" from "Guindaruhan, Minglanilla, Cebu, Philippines")
                $locationParts = explode(',', $barangayData['location']['name']);
                $barangayName = trim($locationParts[0]);
            }

            $barangay = array_merge(['id' => $barangayId], $barangayData);
            $barangay['barangayName'] = $barangayName;

            // Convert PSGC codes to actual location names
            if (isset($barangayData['region']) && isset($barangayData['province']) && isset($barangayData['city'])) {
                $location = $this->getLocationFromPSGC($barangayData['region'], $barangayData['province'], $barangayData['city']);
                if ($location) {
                    $barangay['displayLocation'] = $location;
                }
                
                // Also convert individual location values
                $barangay['displayRegion'] = $this->getLocationNameFromCode('regions', $barangayData['region']);
                $barangay['displayProvince'] = $this->getLocationNameFromCode('provinces', $barangayData['province']);
                $barangay['displayCity'] = $this->getLocationNameFromCode('cities', $barangayData['city']);
            }

            return view('rhu.barangays.show', compact('barangay'));
        } catch (\Exception $e) {
            \Log::error('Error fetching barangay details: ' . $e->getMessage());
            return redirect()->route('rhu.barangays.index')->with('error', 'Failed to load barangay details.');
        }
    }

    /**
     * Generate and send credentials to barangay
     */
    public function sendCredentials($barangayId)
    {
        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify user is from an RHU
        $rhuId = $this->getBarangayId();
        if (!$rhuId) {
            return response()->json(['error' => 'RHU ID not found'], 400);
        }

        try {
            $barangayDoc = $this->firestore
                ->collection('barangay')
                ->document($barangayId)
                ->snapshot();

            if (!$barangayDoc->exists()) {
                return response()->json(['error' => 'Barangay not found'], 404);
            }

            $barangayData = $barangayDoc->data();

            // Verify this barangay belongs to the user's RHU
            if (($barangayData['rhuId'] ?? '') !== $rhuId) {
                return response()->json(['error' => 'You do not have permission to manage this barangay'], 403);
            }

            // Check if barangay already has credentials sent
            if (isset($barangayData['status']) && $barangayData['status'] === 'active') {
                return response()->json(['error' => 'This barangay account is already active.'], 400);
            }

            $barangayEmail = $barangayData['email'] ?? null;
            $healthCenterName = $barangayData['healthCenterName'] ?? 'Health Center';

            if (!$barangayEmail) {
                return response()->json(['error' => 'Barangay email is not set.'], 400);
            }

            // Generate username for the barangay
            $username = 'BHC_' . strtoupper(substr(Str::uuid(), 0, 8));

            // Try to get or create Firebase Auth user
            $uid = null;
            try {
                // Try to create Firebase Auth user
                $authUser = $this->auth->createUser([
                    'email' => $barangayEmail,
                    'displayName' => $healthCenterName,
                    'emailVerified' => false,
                ]);
                $uid = $authUser->uid;
            } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
                // Email already exists, try to get the existing user
                \Log::info('Firebase user already exists for barangay email: ' . $barangayEmail);
                try {
                    $existingUser = $this->auth->getUserByEmail($barangayEmail);
                    $uid = $existingUser->uid;
                } catch (\Exception $getUserException) {
                    \Log::error('Could not get existing Firebase user: ' . $getUserException->getMessage());
                    return response()->json(['error' => 'Email already registered in system'], 400);
                }
            }

            // Update Firestore document with username and UID, status to pending_setup
            $this->firestore->collection('barangay')->document($barangayId)->update([
                ['path' => 'username', 'value' => $username],
                ['path' => 'uid', 'value' => $uid],
                ['path' => 'status', 'value' => 'pending_setup'],
                ['path' => 'approved_by', 'value' => $user['id']],
                ['path' => 'approved_at', 'value' => now()->toDateTimeString()],
            ]);

            // Send setup email with token
            $setupController = new \App\Http\Controllers\Auth\BarangayAccountSetupController();
            $emailSent = $setupController::sendSetupEmail($barangayId, $barangayEmail, $healthCenterName, $username);

            if (!$emailSent) {
                throw new \Exception('Failed to send setup email');
            }

            \Log::info('Barangay credentials sent', [
                'barangay_id' => $barangayId,
                'username' => $username,
                'email' => $barangayEmail,
                'sent_by' => $user['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credentials sent successfully! Setup email has been sent to ' . $barangayEmail,
                'username' => $username,
                'email' => $barangayEmail,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending barangay credentials: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send credentials: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get location name from PSGC code
     */
    private function getLocationNameFromCode($type, $code)
    {
        if (!$code) {
            return null;
        }

        try {
            $url = "https://psgc.gitlab.io/api/{$type}/{$code}.json";
            $response = Http::get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['name'] ?? null;
            }
        } catch (\Exception $e) {
            \Log::debug("Failed to fetch location name for {$type}/{$code}: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Convert PSGC codes to location names
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
