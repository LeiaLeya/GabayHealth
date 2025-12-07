<?php

namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
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
        
        \Log::info('BHC ReportsController index - User session: ' . json_encode($user));
        
        $barangayId = $this->getBarangayId();
        
        \Log::info('BHC ReportsController index - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('BHC ReportsController index - No barangayId available, showing empty reports');
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
        
        $reports = $this->getVerifiedHealthReports($barangayId, $filter, $dateRange, $symptomFilter);
        $heatmapData = $this->processHeatmapData($reports);
        $stats = $this->getStatistics($reports);
        $chartData = $this->getChartData($reports);
        $availableSymptoms = $this->getAvailableSymptoms($barangayId);
        
        return $this->view('reports.index', compact(
            'heatmapData', 
            'stats', 
            'chartData', 
            'filter', 
            'dateRange', 
            'symptomFilter', 
            'availableSymptoms'
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
        
        \Log::info('BHC ReportsController verify - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('BHC ReportsController verify - No barangayId available, showing empty reports');
            return $this->view('reports.verify', [
                'pendingReports' => [],
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
        
        return $this->view('reports.verify', compact('pendingReports', 'stats'));
    }

    public function rejected()
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access rejected reports.');
        }
        
        $barangayId = $this->getBarangayId();
        
        \Log::info('BHC ReportsController rejected - BarangayId for filtering: ' . $barangayId);
        
        if (!$barangayId) {
            \Log::error('BHC ReportsController rejected - No barangayId available, showing empty reports');
            return $this->view('reports.rejected', [
                'rejectedReports' => [],
                'stats' => [
                    'total_rejected' => 0,
                    'rejected_today' => 0,
                    'rejected_this_month' => 0
                ]
            ])->with('warning', 'Unable to determine barangay. Showing empty reports.');
        }
        
        $rejectedReports = $this->getRejectedReports($barangayId);
        $stats = $this->getRejectedStats($barangayId);
        
        return $this->view('reports.rejected', compact('rejectedReports', 'stats'));
    }

    public function approve($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->back()->with('error', 'Please login to approve reports.');
        }
        
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
                return redirect()->back()->with('error', 'You can only approve reports from your barangay.');
            }
            
            $this->firestore
                ->collection("reports")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'verified'],
                    ['path' => 'verified_at', 'value' => now()->toDateTimeString()],
                    ['path' => 'verified_by', 'value' => session('user.name', 'Health Worker')],
                    ['path' => 'verified_by_id', 'value' => session('user.id')]
                ]);

            return redirect()->back()->with('success', 'Report approved successfully!');
        } catch (\Exception $e) {
            \Log::error('Error approving report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve report: ' . $e->getMessage());
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
            \Log::info('BHC ReportsController - Verified reports - Barangay ID: ' . $barangayId);
            \Log::info('BHC ReportsController - Filter: ' . $filter . ', Date Range: ' . $dateRange . ', Symptom: ' . $symptomFilter);
            
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
            
            \Log::info('BHC ReportsController - Verified reports found: ' . count($reports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching verified health reports: ' . $e->getMessage());
        }

        return $reports;
    }

    private function getPendingReports($barangayId)
    {
        $pendingReports = [];
        
        if (!$barangayId) {
            \Log::error('getPendingReports - No barangayId available');
            return $pendingReports;
        }
        
        try {
            \Log::info('BHC ReportsController - Barangay ID: ' . $barangayId);
            
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
                    $pendingReports[] = array_merge($report['data'], ['id' => $report['id']]);
                    \Log::info('Added pending report: ' . $report['id']);
                }
            }
            
            \Log::info('BHC ReportsController - Pending reports found: ' . count($pendingReports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching pending reports: ' . $e->getMessage());
            return [];
        }

        return $pendingReports;
    }

    private function getRejectedReports($barangayId)
    {
        $rejectedReports = [];
        
        if (!$barangayId) {
            \Log::error('getRejectedReports - No barangayId available');
            return $rejectedReports;
        }
        
        try {
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
            
            \Log::info('BHC ReportsController - Rejected reports found: ' . count($rejectedReports));
            
        } catch (\Exception $e) {
            \Log::error('Error fetching rejected reports: ' . $e->getMessage());
            return [];
        }

        return $rejectedReports;
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

                    if ($reportData['createdAt'] && Carbon::parse($reportData['createdAt'])->isSameMonth($today)) {
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

    private function processHeatmapData($reports)
    {
        $heatmapData = [];
        
        $barangayCoordinates = [
            'Cadulawan' => ['lat' => 10.2456, 'lng' => 123.7890, 'cases' => 0],
            'Vito' => ['lat' => 10.2567, 'lng' => 123.8001, 'cases' => 0],
            'Tubod' => ['lat' => 10.2678, 'lng' => 123.8112, 'cases' => 0],
            'Linao' => ['lat' => 10.2789, 'lng' => 123.8223, 'cases' => 0],
            'PAKIGNE' => ['lat' => 10.2890, 'lng' => 123.8334, 'cases' => 0],
            'Manduang' => ['lat' => 10.3001, 'lng' => 123.8445, 'cases' => 0],
            'Camp 7' => ['lat' => 10.3112, 'lng' => 123.8556, 'cases' => 0],
            'Cuanos' => ['lat' => 10.3223, 'lng' => 123.8667, 'cases' => 0],
            'Tunghaan' => ['lat' => 10.3334, 'lng' => 123.8778, 'cases' => 0],
            'Pob. Ward I' => ['lat' => 10.3445, 'lng' => 123.8889, 'cases' => 0],
            'Pob. Ward II' => ['lat' => 10.3556, 'lng' => 123.9000, 'cases' => 0],
            'Calajoan' => ['lat' => 10.3667, 'lng' => 123.9111, 'cases' => 0],
            'Guindarohan' => ['lat' => 10.3778, 'lng' => 123.9222, 'cases' => 0],
            'Pob. Ward III' => ['lat' => 10.3889, 'lng' => 123.9333, 'cases' => 0],
            'Tulay' => ['lat' => 10.4000, 'lng' => 123.9444, 'cases' => 0],
            'Camp B' => ['lat' => 10.4111, 'lng' => 123.9555, 'cases' => 0],
            'Pob. Ward IV' => ['lat' => 10.4222, 'lng' => 123.9666, 'cases' => 0],
            'Tungkap' => ['lat' => 10.4333, 'lng' => 123.9777, 'cases' => 0],
        ];

        foreach ($reports as $report) {
            $barangay = $this->getBarangayNameFromId($report['barangayId'] ?? '') ?? 'Unknown';
            if (isset($barangayCoordinates[$barangay])) {
                $barangayCoordinates[$barangay]['cases']++;
            }
        }

        foreach ($barangayCoordinates as $barangay => $data) {
            if ($data['cases'] > 0) {
                $heatmapData[] = [
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    'weight' => $data['cases'],
                    'barangay' => $barangay,
                    'cases' => $data['cases']
                ];
            }
        }

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

