<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportsController extends Controller
{
    use HasRoleContext;

    protected $firestore;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
    }

    public function index()
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access reports.');
        }
        
        \Log::info('RHU ReportsController index - User session: ' . json_encode($user));
        
        $barangayId = $this->getBarangayId();
        
        \Log::info('RHU ReportsController index - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('RHU ReportsController index - No barangayId available, showing empty reports');
            return $this->view('reports.index', [
                'heatmapData' => [],
                'stats' => [
                    'total_cases' => 0,
                    'fever_cases' => 0,
                    'dengue_cases' => 0,
                    'diarrhea_cases' => 0,
                    'rash_cases' => 0,
                    'cough_cases' => 0,
                    'headache_cases' => 0,
                    'top_barangay' => 'None',
                    'top_cases' => 0,
                    'recent_cases' => 0
                ],
                'chartData' => [
                    'labels' => [],
                    'datasets' => [
                        'fever' => [],
                        'dengue' => [],
                        'diarrhea' => [],
                        'cough' => [],
                        'headache' => []
                    ]
                ],
                'filter' => 'all',
                'dateRange' => 'month',
                'symptomFilter' => 'all',
                'availableSymptoms' => ['Fever', 'Dengue', 'Diarrhea', 'Cough', 'Headache']
            ])->with('warning', 'Unable to determine barangay. Showing empty reports.');
        }
        
        $filter = request('filter', 'all');
        $dateRange = request('date_range', 'month');
        $symptomFilter = request('symptom', 'all');

        $cacheKey = sprintf(
            'reports:index:rhu:%s:%s:%s:%s',
            $barangayId ?? 'none',
            $filter,
            $dateRange,
            $symptomFilter
        );

        $payload = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($filter, $dateRange, $symptomFilter, $barangayId) {
            // Fetch ALL verified reports from ALL barangays for the heatmap
            $reports = $this->getAllVerifiedHealthReports($filter, $dateRange, $symptomFilter);
            $barangays = $this->getAllBarangaysWithCoordinates();
            $heatmapData = $this->processHeatmapData($reports, $barangays);

            // Determine initial map center from the current user's barangay if available
            $centerLat = 10.2456;
            $centerLng = 123.7890;
            if ($barangayId && isset($barangays[$barangayId])) {
                $centerLat = $barangays[$barangayId]['lat'] ?? $centerLat;
                $centerLng = $barangays[$barangayId]['lng'] ?? $centerLng;
            }

            return [
                'heatmapData' => $heatmapData,
                'stats' => $this->getStatistics($reports),
                'chartData' => $this->getChartData($reports),
                'availableSymptoms' => $this->getAvailableSymptoms($barangayId),
                'centerLat' => $centerLat,
                'centerLng' => $centerLng,
            ];
        });

        $heatmapData = $payload['heatmapData'];
        $stats = $payload['stats'];
        $chartData = $payload['chartData'];
        $availableSymptoms = $payload['availableSymptoms'];
        $centerLat = $payload['centerLat'];
        $centerLng = $payload['centerLng'];
        
        return $this->view('reports.index', compact(
            'heatmapData', 
            'stats', 
            'chartData', 
            'filter', 
            'dateRange', 
            'symptomFilter', 
            'availableSymptoms',
            'centerLat',
            'centerLng'
        ));
    }

    public function verify()
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access reports verification.');
        }
        
        $barangayId = $this->getBarangayId();
        
        \Log::info('RHU ReportsController verify - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('RHU ReportsController verify - No barangayId available, showing empty reports');
            return $this->view('reports.verify', [
                'pendingReports' => [],
                'barangayNames' => [],
                'stats' => [
                    'pending' => 0,
                    'verified_today' => 0,
                    'rejected_today' => 0,
                    'total_this_month' => 0
                ]
            ])->with('warning', 'Unable to determine barangay. Showing empty reports.');
        }
        
        $pendingReports = $this->getPendingReports($barangayId);
        $stats = $this->getVerificationStats($barangayId);
        $staffAccounts = $this->getStaffAccounts($user['id'], $user['role']);
        $barangayNames = $this->getBarangayNamesForReports($pendingReports);
        
        return $this->view('reports.verify', compact('pendingReports', 'stats', 'staffAccounts', 'barangayNames'));
    }

    public function rejected()
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access rejected reports.');
        }
        
        $barangayId = $this->getBarangayId();
        
        \Log::info('RHU ReportsController rejected - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('RHU ReportsController rejected - No barangayId available, showing empty reports');
            return $this->view('reports.rejected', [
                'rejectedReports' => [],
                'barangayNames' => [],
                'stats' => [
                    'total_rejected' => 0,
                    'rejected_today' => 0,
                    'rejected_this_month' => 0
                ]
            ])->with('warning', 'Unable to determine barangay. Showing empty reports.');
        }
        
        $rejectedReports = $this->getRejectedReports($barangayId);
        $stats = $this->getRejectedStats($barangayId);
        $barangayNames = $this->getBarangayNamesForReports($rejectedReports);
        
        return $this->view('reports.rejected', compact('rejectedReports', 'stats', 'barangayNames'));
    }

    public function verified()
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access verified reports.');
        }
        
        $barangayId = $this->getBarangayId();
        
        \Log::info('RHU ReportsController verified - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('RHU ReportsController verified - No barangayId available, showing empty reports');
            return $this->view('reports.verified', [
                'verifiedReports' => [],
                'barangayNames' => []
            ])->with('warning', 'Unable to determine barangay. Showing empty reports.');
        }
        
        $verifiedReports = $this->getVerifiedReports($barangayId);
        $barangayNames = $this->getBarangayNamesForReports($verifiedReports);
        
        return $this->view('reports.verified', compact('verifiedReports', 'barangayNames'));
    }

    public function approve(Request $request, $id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->back()->with('error', 'Please login to verify reports.');
        }
        
        $request->validate([
            'verified_by' => 'required|string|max:255',
        ]);
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Unable to determine barangay. Please contact your administrator.');
        }
        
        try {
            // Get the verifier's name from the request
            $verifierName = $request->input('verified_by');
            
            if (!$verifierName) {
                return redirect()->back()->with('error', 'Please select a health worker who verified this report.');
            }
            
            // Verify that the selected staff member exists and belongs to this barangay
            $staffAccounts = $this->getStaffAccounts($user['id'], $user['role']);
            $isValidStaff = false;
            foreach ($staffAccounts as $staff) {
                if ($staff['name'] === $verifierName) {
                    $isValidStaff = true;
                    break;
                }
            }
            
            if (!$isValidStaff) {
                return redirect()->back()->with('error', 'Invalid health worker selected. Please select a valid staff member.');
            }
            
            $reportDoc = $this->firestore
                ->collection("reports")
                ->document($id)
                ->snapshot();
            
            if (!$reportDoc->exists()) {
                return redirect()->back()->with('error', 'Report not found.');
            }
            
            $reportData = $reportDoc->data();
            if ($reportData['barangayId'] !== $barangayId) {
                return redirect()->back()->with('error', 'You can only verify reports from your barangay.');
            }
            
            $this->firestore
                ->collection("reports")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'verified'],
                    ['path' => 'verified_at', 'value' => now()->toDateTimeString()],
                    ['path' => 'verified_by', 'value' => $verifierName],
                    ['path' => 'verified_by_id', 'value' => $user['id']]
                ]);

            return redirect()->back()->with('success', 'Report verified successfully!');
        } catch (\Exception $e) {
            \Log::error('Error verifying report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to verify report: ' . $e->getMessage());
        }
    }
    
    private function getVerifierName($user)
    {
        try {
            $userId = $user['id'] ?? null;
            $userRole = $user['role'] ?? null;
            $barangayId = $user['barangayId'] ?? $userId ?? null;
            
            if (!$userId || !$userRole) {
                \Log::error('Missing user ID or role in session');
                return null;
            }
            
            // First, try to find staff member in accounts subcollection using Firebase UID
            // Staff accounts have a 'uid' field that matches Firebase Auth UID
            if ($barangayId && in_array($userRole, ['barangay', 'rhu'])) {
                // Check if user has a Firebase UID in session (from Firebase Auth)
                $firebaseUid = $user['uid'] ?? $user['firebase_uid'] ?? null;
                
                if ($firebaseUid) {
                    // Search in the accounts subcollection for staff with this UID
                    $accounts = $this->firestore
                        ->collection($userRole)
                        ->document($barangayId)
                        ->collection('accounts')
                        ->where('uid', '=', $firebaseUid)
                        ->documents();
                    
                    foreach ($accounts as $account) {
                        if ($account->exists()) {
                            $data = $account->data();
                            $name = $data['name'] ?? null;
                            if ($name) {
                                \Log::info('Found staff member name: ' . $name);
                                return $name;
                            }
                        }
                    }
                }
                
                // If not found by UID, try searching by email if available
                $userEmail = $user['email'] ?? null;
                if ($userEmail) {
                    $accounts = $this->firestore
                        ->collection($userRole)
                        ->document($barangayId)
                        ->collection('accounts')
                        ->where('email', '=', $userEmail)
                        ->documents();
                    
                    foreach ($accounts as $account) {
                        if ($account->exists()) {
                            $data = $account->data();
                            $name = $data['name'] ?? null;
                            if ($name) {
                                \Log::info('Found staff member name by email: ' . $name);
                                return $name;
                            }
                        }
                    }
                }
            }
            
            // Fallback: For barangay/rhu users, fetch from main collection
            $userDoc = $this->firestore
                ->collection($userRole)
                ->document($userId)
                ->snapshot();
            
            if ($userDoc->exists()) {
                $data = $userDoc->data();
                $name = $data['healthCenterName'] ?? $data['name'] ?? $data['barangay'] ?? null;
                if ($name) {
                    \Log::info('Found user name from main collection: ' . $name);
                    return $name;
                }
            }
            
            // Last resort: use name from session
            $name = $user['name'] ?? null;
            if ($name) {
                \Log::info('Using name from session: ' . $name);
                return $name;
            }
            
            \Log::warning('Could not find verifier name for user: ' . $userId . ' with role: ' . $userRole);
            return null;
        } catch (\Exception $e) {
            \Log::error('Error fetching verifier name: ' . $e->getMessage());
            return null;
        }
    }

    public function reject(Request $request, $id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->back()->with('error', 'Please login to reject reports.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Unable to determine barangay. Please contact your administrator.');
        }
        
        try {
            $reportDoc = $this->firestore
                ->collection("reports")
                ->document($id)
                ->snapshot();
            
            if (!$reportDoc->exists()) {
                return redirect()->back()->with('error', 'Report not found.');
            }
            
            $reportData = $reportDoc->data();
            if ($reportData['barangayId'] !== $barangayId) {
                return redirect()->back()->with('error', 'You can only reject reports from your barangay.');
            }
            
            $this->firestore
                ->collection("reports")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'rejected'],
                    ['path' => 'rejected_at', 'value' => now()->toDateTimeString()],
                    ['path' => 'rejected_by', 'value' => session('user.name', 'Health Worker')],
                    ['path' => 'rejected_by_id', 'value' => session('user.id')],
                    ['path' => 'rejection_reason', 'value' => $request->rejection_reason]
                ]);

            return redirect()->back()->with('success', 'Report rejected successfully!');
        } catch (\Exception $e) {
            \Log::error('Error rejecting report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject report: ' . $e->getMessage());
        }
    }

    private function getVerifiedHealthReports($barangayId, $filter, $dateRange, $symptomFilter)
    {
        $reports = [];
        
        if (!$barangayId) {
            \Log::error('getVerifiedHealthReports - No barangayId available');
            return $reports;
        }
        
        try {
            \Log::info('RHU ReportsController - Verified reports - Barangay ID: ' . $barangayId);
            \Log::info('RHU ReportsController - Filter: ' . $filter . ', Date Range: ' . $dateRange . ', Symptom: ' . $symptomFilter);
            
            $endDate = Carbon::now();
            switch ($dateRange) {
                case 'week':
                    $startDate = $endDate->copy()->subWeek();
                    break;
                case 'month':
                    $startDate = $endDate->copy()->subMonth();
                    break;
                case 'quarter':
                    $startDate = $endDate->copy()->subQuarter();
                    break;
                case 'year':
                    $startDate = $endDate->copy()->subYear();
                    break;
                default:
                    $startDate = $endDate->copy()->subMonth();
            }

            // Fetch from main reports collection, filtered by barangayId
            $documents = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->where('status', '=', 'verified')
                ->documents();

            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $reportDate = Carbon::parse($reportData['startDate'] ?? $reportData['createdAt'] ?? '');
                    
                    if ($reportDate->between($startDate, $endDate)) {
                        if ($filter === 'all' || $this->matchesCondition($reportData, $filter)) {
                            if ($symptomFilter === 'all' || $this->hasSymptom($reportData, $symptomFilter)) {
                                $reports[] = array_merge($reportData, ['id' => $doc->id()]);
                            }
                        }
                    }
                }
            }
            
            \Log::info('RHU ReportsController - Verified reports found: ' . count($reports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching verified health reports: ' . $e->getMessage());
        }

        return $reports;
    }

    private function getAllVerifiedHealthReports($filter, $dateRange, $symptomFilter)
    {
        $reports = [];
        
        try {
            \Log::info('RHU ReportsController - Fetching ALL verified reports - Filter: ' . $filter . ', Date Range: ' . $dateRange . ', Symptom: ' . $symptomFilter);
            
            $endDate = Carbon::now();
            switch ($dateRange) {
                case 'week':
                    $startDate = $endDate->copy()->subWeek();
                    break;
                case 'month':
                    $startDate = $endDate->copy()->subMonth();
                    break;
                case 'quarter':
                    $startDate = $endDate->copy()->subQuarter();
                    break;
                case 'year':
                    $startDate = $endDate->copy()->subYear();
                    break;
                default:
                    $startDate = $endDate->copy()->subYear(); // Default to 1 year to show more reports
            }

            // Fetch ALL verified reports from ALL barangays
            $documents = $this->firestore
                ->collection("reports")
                ->where('status', '=', 'verified')
                ->documents();

            $totalDocs = 0;
            $filteredByDate = 0;
            $filteredByCondition = 0;
            $filteredBySymptom = 0;

            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $totalDocs++;
                    $reportData = $doc->data();
                    
                    // Try to get date from verified_at, startDate, or createdAt
                    $dateField = $reportData['verified_at'] ?? $reportData['startDate'] ?? $reportData['createdAt'] ?? null;
                    
                    if ($dateField) {
                        try {
                            $reportDate = Carbon::parse($dateField);
                            
                            // Check date range - use verified_at if available, otherwise use startDate/createdAt
                            if (!$reportDate->between($startDate, $endDate)) {
                                $filteredByDate++;
                                continue;
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Error parsing date for report ' . $doc->id() . ': ' . $e->getMessage());
                            // Include report even if date parsing fails
                        }
                    } else {
                        // If no date field, include the report anyway
                        \Log::warning('Report ' . $doc->id() . ' has no date field');
                    }
                    
                    // Check condition filter
                    if ($filter !== 'all' && !$this->matchesCondition($reportData, $filter)) {
                        $filteredByCondition++;
                        continue;
                    }
                    
                    // Check symptom filter
                    if ($symptomFilter !== 'all' && !$this->hasSymptom($reportData, $symptomFilter)) {
                        $filteredBySymptom++;
                        continue;
                    }
                    
                    $reports[] = array_merge($reportData, ['id' => $doc->id()]);
                }
            }
            
            \Log::info('RHU ReportsController - Total verified docs: ' . $totalDocs . ', Filtered by date: ' . $filteredByDate . ', Filtered by condition: ' . $filteredByCondition . ', Filtered by symptom: ' . $filteredBySymptom . ', Final reports: ' . count($reports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching all verified health reports: ' . $e->getMessage());
        }

        return $reports;
    }

    private function getAllBarangaysWithCoordinates()
    {
        $barangays = [];
        
        try {
            $documents = $this->firestore
                ->collection("barangay")
                ->documents();

            $totalBarangays = 0;
            $withLocation = 0;
            $withoutLocation = 0;

            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $totalBarangays++;
                    $data = $doc->data();
                    $location = $data['location'] ?? null;
                    
                    if ($location) {
                        // Handle GeoPoint object from Firestore
                        if (is_object($location) && method_exists($location, 'latitude') && method_exists($location, 'longitude')) {
                            $barangays[$doc->id()] = [
                                'id' => $doc->id(),
                                'name' => $data['healthCenterName'] ?? $data['name'] ?? 'Unknown',
                                'lat' => $location->latitude(),
                                'lng' => $location->longitude()
                            ];
                            $withLocation++;
                        } 
                        // Handle array format (fallback)
                        elseif (is_array($location) && isset($location['latitude']) && isset($location['longitude'])) {
                            $barangays[$doc->id()] = [
                                'id' => $doc->id(),
                                'name' => $data['healthCenterName'] ?? $data['name'] ?? 'Unknown',
                                'lat' => $location['latitude'],
                                'lng' => $location['longitude']
                            ];
                            $withLocation++;
                        } else {
                            $withoutLocation++;
                            \Log::warning('Barangay ' . $doc->id() . ' has location but format is not recognized. Type: ' . gettype($location));
                        }
                    } else {
                        $withoutLocation++;
                        \Log::warning('Barangay ' . $doc->id() . ' (' . ($data['healthCenterName'] ?? $data['name'] ?? 'Unknown') . ') has no location field');
                    }
                }
            }
            
            \Log::info('RHU ReportsController - Total barangays: ' . $totalBarangays . ', With coordinates: ' . $withLocation . ', Without coordinates: ' . $withoutLocation);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching barangays with coordinates: ' . $e->getMessage());
        }

        return $barangays;
    }

    private function getPendingReports($barangayId)
    {
        $pendingReports = [];
        
        if (!$barangayId) {
            \Log::error('getPendingReports - No barangayId available');
            return $pendingReports;
        }
        
        try {
            \Log::info('RHU ReportsController - Barangay ID: ' . $barangayId);
            
            // Fetch from main reports collection, filtered by barangayId
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();
            
            $allReports = [];
            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $allReports[] = [
                        'id' => $doc->id(),
                        'status' => $reportData['status'] ?? 'unknown',
                        'data' => $reportData
                    ];
                    \Log::info('All report - ID: ' . $doc->id() . ' - Status: ' . ($reportData['status'] ?? 'unknown'));
                }
            }
            
            \Log::info('Total reports found: ' . count($allReports));
            
            foreach ($allReports as $report) {
                $status = $report['status'];
                \Log::info('Checking report ' . $report['id'] . ' with status: "' . $status . '"');
                
                if ($status === 'to be reviewed') {
                    $data = $report['data'];

                    $normalized = [
                        'id' => $report['id'],
                        'barangayId' => $barangayId,
                        'symptoms' => isset($data['condition']) ? [(string)$data['condition']] : ($data['symptoms'] ?? []),
                        'affectedPerson' => $data['reported_by'] ?? ($data['affectedPerson'] ?? 'Unknown'),
                        'startDate' => $data['date'] ?? ($data['startDate'] ?? null),
                        'additionalInfo' => $data['description'] ?? ($data['additionalInfo'] ?? null),
                        'createdAt' => $data['date'] ?? ($data['createdAt'] ?? null),
                        'location' => $data['location'] ?? null,
                        'cases' => $data['cases'] ?? null,
                        'status' => $status,
                    ];

                    $pendingReports[] = array_merge($data, $normalized);
                    \Log::info('Added pending report: ' . $report['id']);
                }
            }
            
            \Log::info('RHU ReportsController - Pending reports found: ' . count($pendingReports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching pending reports: ' . $e->getMessage());
            return [];
        }

        return $pendingReports;
    }

    private function getStaffAccounts($userId, $userRole)
    {
        try {
            $documents = $this->firestore
                ->collection($userRole)
                ->document($userId)
                ->collection('accounts')
                ->documents();

            $staffAccounts = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $data = $document->data();
                    $staffRole = $data['role'] ?? '';
                    // Only include active staff members (nurse, bhw, midwife, doctor)
                    if (in_array($staffRole, ['nurse', 'bhw', 'midwife', 'doctor']) && 
                        ($data['status'] ?? 'active') === 'active') {
                        $staffAccounts[] = [
                            'id' => $document->id(),
                            'name' => $data['name'] ?? 'Unknown',
                            'role' => $staffRole
                        ];
                    }
                }
            }

            // Sort by name
            usort($staffAccounts, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $staffAccounts;
        } catch (\Exception $e) {
            \Log::error('Error fetching staff accounts: ' . $e->getMessage());
            return [];
        }
    }

    private function getRejectedReports($barangayId)
    {
        $rejectedReports = [];
        
        if (!$barangayId) {
            \Log::error('getRejectedReports - No barangayId available');
            return $rejectedReports;
        }
        
        try {
            // Fetch from main reports collection, filtered by barangayId
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();
            
            $allReports = [];
            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $allReports[] = [
                        'id' => $doc->id(),
                        'status' => $reportData['status'] ?? 'unknown',
                        'data' => $reportData
                    ];
                }
            }
            
            foreach ($allReports as $report) {
                $status = $report['status'];
                
                if ($status === 'rejected') {
                    $rejectedReports[] = array_merge($report['data'], ['id' => $report['id']]);
                }
            }
            
            usort($rejectedReports, function($a, $b) {
                $dateA = $a['rejected_at'] ?? $a['createdAt'] ?? '';
                $dateB = $b['rejected_at'] ?? $b['createdAt'] ?? '';
                return strtotime($dateB) - strtotime($dateA);
            });
            
            \Log::info('RHU ReportsController - Rejected reports found: ' . count($rejectedReports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching rejected reports: ' . $e->getMessage());
            return [];
        }

        return $rejectedReports;
    }

    private function getVerifiedReports($barangayId)
    {
        $verifiedReports = [];
        
        if (!$barangayId) {
            \Log::error('getVerifiedReports - No barangayId available');
            return $verifiedReports;
        }
        
        try {
            \Log::info('RHU ReportsController - Fetching verified reports for Barangay ID: ' . $barangayId);
            
            // Fetch from main reports collection, filtered by barangayId and verified status
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->where('status', '=', 'verified')
                ->documents();
            
            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $verifiedReports[] = array_merge($reportData, [
                        'id' => $doc->id(),
                        'verified_at' => $reportData['verified_at'] ?? null
                    ]);
                }
            }
            
            // Sort by verified_at date (newest first)
            usort($verifiedReports, function($a, $b) {
                $dateA = $a['verified_at'] ?? $a['createdAt'] ?? '';
                $dateB = $b['verified_at'] ?? $b['createdAt'] ?? '';
                return strtotime($dateB) - strtotime($dateA);
            });
            
            \Log::info('RHU ReportsController - Verified reports found: ' . count($verifiedReports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching verified reports: ' . $e->getMessage());
            return [];
        }

        return $verifiedReports;
    }

    private function getRejectedStats($barangayId)
    {
        try {
            $stats = [
                'total_rejected' => 0,
                'rejected_today' => 0,
                'rejected_this_month' => 0
            ];
            
            if (!$barangayId) {
                \Log::error('getRejectedStats - No barangayId available');
                return $stats;
            }
            
            $today = Carbon::today();
            
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();
            
            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $status = $reportData['status'] ?? 'unknown';
                    
                    if ($status === 'rejected') {
                        $stats['total_rejected']++;
                        
                        if (isset($reportData['rejected_at'])) {
                            $rejectedAt = Carbon::parse($reportData['rejected_at']);
                            if ($rejectedAt->isToday()) {
                                $stats['rejected_today']++;
                            }
                            if ($rejectedAt->isSameMonth($today)) {
                                $stats['rejected_this_month']++;
                            }
                        }
                    }
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            \Log::error('Error getting rejected stats: ' . $e->getMessage());
            return ['total_rejected' => 0, 'rejected_today' => 0, 'rejected_this_month' => 0];
        }
    }

    private function getVerificationStats($barangayId)
    {
        try {
            $stats = [
                'pending' => 0,
                'verified_today' => 0,
                'rejected_today' => 0,
                'total_this_month' => 0
            ];
            
            if (!$barangayId) {
                \Log::error('getVerificationStats - No barangayId available');
                return $stats;
            }
            
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();
            
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();
            
            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $status = $reportData['status'] ?? 'unknown';
                    
                    \Log::info('Stats - Report ' . $doc->id() . ' has status: "' . $status . '"');
                    
                    if ($status === 'to be reviewed') {
                        $stats['pending']++;
                        \Log::info('Stats - Found pending report: ' . $doc->id());
                    }
                    
                    if ($status === 'verified' && isset($reportData['verified_at'])) {
                        $verifiedAt = Carbon::parse($reportData['verified_at']);
                        if ($verifiedAt->isToday()) {
                            $stats['verified_today']++;
                        }
                    }
                    
                    if ($status === 'rejected' && isset($reportData['rejected_at'])) {
                        $rejectedAt = Carbon::parse($reportData['rejected_at']);
                        if ($rejectedAt->isToday()) {
                            $stats['rejected_today']++;
                        }
                    }

                    if (!empty($reportData['createdAt']) && Carbon::parse($reportData['createdAt'])->isSameMonth($today)) {
                        $stats['total_this_month']++;
                    }
                }
            }
            
            \Log::info('Verification stats: ' . json_encode($stats));
            return $stats;
        } catch (\Exception $e) {
            \Log::error('Error getting verification stats: ' . $e->getMessage());
            return ['pending' => 0, 'verified_today' => 0, 'rejected_today' => 0, 'total_this_month' => 0];
        }
    }

    private function matchesCondition($reportData, $filter)
    {
        $symptoms = $reportData['symptoms'] ?? [];
        if (is_array($symptoms)) {
            foreach ($symptoms as $symptom) {
                if (strtolower($symptom) === strtolower($filter)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasSymptom($reportData, $symptom)
    {
        $symptoms = $reportData['symptoms'] ?? [];
        if (is_array($symptoms)) {
            foreach ($symptoms as $s) {
                if (strtolower($s) === strtolower($symptom)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getAvailableSymptoms($barangayId)
    {
        try {
            $symptoms = [];
            
            if (!$barangayId) {
                \Log::error('getAvailableSymptoms - No barangayId available');
                return ['Fever', 'Dengue', 'Diarrhea', 'Cough', 'Headache'];
            }
            
            $documents = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->where('status', '=', 'verified')
                ->limit(100)
                ->documents();
            
            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $reportSymptoms = $reportData['symptoms'] ?? [];
                    
                    if (is_array($reportSymptoms)) {
                        foreach ($reportSymptoms as $symptom) {
                            $symptoms[strtolower($symptom)] = ucfirst($symptom);
                        }
                    }
                }
            }
            
            return array_values($symptoms);
        } catch (\Exception $e) {
            \Log::error('Error getting available symptoms: ' . $e->getMessage());
            return ['Fever', 'Dengue', 'Diarrhea', 'Cough', 'Headache'];
        }
    }

    private function processHeatmapData($reports, $barangays)
    {
        $heatmapData = [];
        
        \Log::info('RHU ReportsController - Processing heatmap data. Reports: ' . count($reports) . ', Barangays: ' . count($barangays));
        
        // Group reports by barangay ID and count cases/symptoms
        $barangayStats = [];
        $reportsWithoutBarangay = 0;
        $reportsWithUnknownBarangay = 0;
        
        foreach ($reports as $report) {
            $barangayId = $report['barangayId'] ?? null;
            
            if (!$barangayId) {
                $reportsWithoutBarangay++;
                \Log::warning('Report ' . ($report['id'] ?? 'unknown') . ' has no barangayId');
                continue;
            }
            
            if (!isset($barangays[$barangayId])) {
                $reportsWithUnknownBarangay++;
                \Log::warning('Report ' . ($report['id'] ?? 'unknown') . ' has barangayId ' . $barangayId . ' but barangay not found in barangays list');
                continue;
            }
            
            if (!isset($barangayStats[$barangayId])) {
                $barangayStats[$barangayId] = [
                    'cases' => 0,
                    'symptoms' => []
                ];
            }
            
            $barangayStats[$barangayId]['cases']++;
            
            // Collect symptoms
            $symptoms = $report['symptoms'] ?? [];
            if (is_array($symptoms)) {
                foreach ($symptoms as $symptom) {
                    $symptom = strtolower(trim($symptom));
                    if ($symptom && !in_array($symptom, $barangayStats[$barangayId]['symptoms'])) {
                        $barangayStats[$barangayId]['symptoms'][] = $symptom;
                    }
                }
            }
        }

        \Log::info('RHU ReportsController - Reports without barangayId: ' . $reportsWithoutBarangay . ', Reports with unknown barangay: ' . $reportsWithUnknownBarangay . ', Barangays with stats: ' . count($barangayStats));

        // Create heatmap data for each barangay with verified reports
        foreach ($barangayStats as $barangayId => $stats) {
            if (isset($barangays[$barangayId]) && $stats['cases'] > 0) {
                $barangay = $barangays[$barangayId];
                $heatmapData[] = [
                    'lat' => $barangay['lat'],
                    'lng' => $barangay['lng'],
                    'weight' => $stats['cases'],
                    'barangay' => $barangay['name'],
                    'barangayId' => $barangayId,
                    'cases' => $stats['cases'],
                    'symptoms' => $stats['symptoms']
                ];
            }
        }

        \Log::info('RHU ReportsController - Final heatmap data points: ' . count($heatmapData));

        return $heatmapData;
    }

    private function getBarangayNameFromId($barangayId)
    {
        try {
            $barangayDoc = $this->firestore
                ->collection("barangay")
                ->document($barangayId)
                ->snapshot();
            
            if ($barangayDoc->exists()) {
                $data = $barangayDoc->data();
                return $data['healthCenterName'] ?? $data['barangay'] ?? 'Unknown';
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching barangay name: ' . $e->getMessage());
        }
        
        return 'Unknown';
    }

    private function getBarangayNamesForReports(array $reports): array
    {
        $barangayNames = [];
        $barangayIds = collect($reports)
            ->pluck('barangayId')
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($barangayIds as $barangayId) {
            $barangayNames[$barangayId] = $this->getBarangayNameFromId($barangayId);
        }

        return $barangayNames;
    }

    private function getStatistics($reports)
    {
        $stats = [
            'total_cases' => count($reports),
            'fever_cases' => 0,
            'dengue_cases' => 0,
            'diarrhea_cases' => 0,
            'rash_cases' => 0,
            'cough_cases' => 0,
            'headache_cases' => 0,
            'top_barangay' => 'None',
            'top_cases' => 0,
            'recent_cases' => 0
        ];

        $barangayCounts = [];
        $recentDate = Carbon::now()->subDays(7);

        foreach ($reports as $report) {
            $symptoms = $report['symptoms'] ?? [];
            $barangay = $this->getBarangayNameFromId($report['barangayId'] ?? '') ?? 'Unknown';
            $reportDate = Carbon::parse($report['startDate'] ?? $report['createdAt'] ?? '');

            if (is_array($symptoms)) {
                foreach ($symptoms as $symptom) {
                    $symptom = strtolower($symptom);
                    switch ($symptom) {
                        case 'fever':
                            $stats['fever_cases']++;
                            break;
                        case 'dengue':
                            $stats['dengue_cases']++;
                            break;
                        case 'diarrhea':
                            $stats['diarrhea_cases']++;
                            break;
                        case 'rash':
                            $stats['rash_cases']++;
                            break;
                        case 'cough':
                            $stats['cough_cases']++;
                            break;
                        case 'headache':
                            $stats['headache_cases']++;
                            break;
                    }
                }
            }

            if (!isset($barangayCounts[$barangay])) {
                $barangayCounts[$barangay] = 0;
            }
            $barangayCounts[$barangay]++;

            if ($reportDate->gte($recentDate)) {
                $stats['recent_cases']++;
            }
        }

        if (!empty($barangayCounts)) {
            $topBarangay = array_keys($barangayCounts, max($barangayCounts))[0];
            $stats['top_barangay'] = $topBarangay;
            $stats['top_cases'] = $barangayCounts[$topBarangay];
        }

        return $stats;
    }

    private function getChartData($reports)
    {
        $chartData = [
            'labels' => [],
            'datasets' => [
                'fever' => [],
                'dengue' => [],
                'diarrhea' => [],
                'cough' => [],
                'headache' => []
            ]
        ];

        $barangayData = [];

        foreach ($reports as $report) {
            $barangay = $this->getBarangayNameFromId($report['barangayId'] ?? '') ?? 'Unknown';
            $symptoms = $report['symptoms'] ?? [];

            if (!isset($barangayData[$barangay])) {
                $barangayData[$barangay] = [
                    'fever' => 0,
                    'dengue' => 0,
                    'diarrhea' => 0,
                    'cough' => 0,
                    'headache' => 0
                ];
            }

            if (is_array($symptoms)) {
                foreach ($symptoms as $symptom) {
                    $symptom = strtolower($symptom);
                    if (in_array($symptom, ['fever', 'dengue', 'diarrhea', 'cough', 'headache'])) {
                        $barangayData[$barangay][$symptom]++;
                    }
                }
            }
        }

        foreach ($barangayData as $barangay => $data) {
            $chartData['labels'][] = $barangay;
            $chartData['datasets']['fever'][] = $data['fever'];
            $chartData['datasets']['dengue'][] = $data['dengue'];
            $chartData['datasets']['diarrhea'][] = $data['diarrhea'];
            $chartData['datasets']['cough'][] = $data['cough'];
            $chartData['datasets']['headache'][] = $data['headache'];
        }

        return $chartData;
    }
}


