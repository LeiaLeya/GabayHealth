<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $barangayId = $this->getBarangayId();

        if (!$barangayId) {
            return $this->view('reports.index', [
                'heatmapData' => [],
                'verifiedBubbleData' => [],
                'unverifiedBubbleData' => [],
                'hotspotData' => [],
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
            $verifiedReports = $this->getAllVerifiedHealthReports($filter, $dateRange, $symptomFilter);
            $resolvedReports = $this->getAllResolvedHealthReports($filter, $dateRange, $symptomFilter);
            $unverifiedReports = $this->getAllUnverifiedSymptomSignals($filter, $dateRange, $symptomFilter);
            $barangays = $this->getAllBarangaysWithCoordinates();
            $heatmapData = $this->processHeatmapData($verifiedReports, $barangays);
            $verifiedBubbleData = $this->processVerifiedBubbleData($verifiedReports, $resolvedReports, $barangays);
            $unverifiedBubbleData = $this->processUnverifiedBubbleData($unverifiedReports, $barangays);
            $hotspotData = $this->buildHotspotData($verifiedBubbleData);

            $centerLat = 10.2456;
            $centerLng = 123.7890;
            if ($barangayId && isset($barangays[$barangayId])) {
                $centerLat = $barangays[$barangayId]['lat'] ?? $centerLat;
                $centerLng = $barangays[$barangayId]['lng'] ?? $centerLng;
            }

            return [
                'heatmapData' => $heatmapData,
                'verifiedBubbleData' => $verifiedBubbleData,
                'unverifiedBubbleData' => $unverifiedBubbleData,
                'hotspotData' => $hotspotData,
                'stats' => $this->getStatistics($verifiedReports),
                'chartData' => $this->getChartData($verifiedReports),
                'availableSymptoms' => $this->getAvailableSymptoms($barangayId),
                'centerLat' => $centerLat,
                'centerLng' => $centerLng,
            ];
        });

        $heatmapData = $payload['heatmapData'];
        $verifiedBubbleData = $payload['verifiedBubbleData'];
        $unverifiedBubbleData = $payload['unverifiedBubbleData'];
        $hotspotData = $payload['hotspotData'];
        $stats = $payload['stats'];
        $chartData = $payload['chartData'];
        $availableSymptoms = $payload['availableSymptoms'];
        $centerLat = $payload['centerLat'];
        $centerLng = $payload['centerLng'];

        return $this->view('reports.index', compact(
            'heatmapData',
            'verifiedBubbleData',
            'unverifiedBubbleData',
            'hotspotData',
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

        if (!$barangayId) {
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
        $perPage = 6;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pendingReportsCollection = collect($pendingReports);
        $pendingReports = new LengthAwarePaginator(
            $pendingReportsCollection->forPage($currentPage, $perPage)->values(),
            $pendingReportsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
        $stats = $this->getVerificationStats($barangayId);
        $staffAccounts = $this->getStaffAccounts($user['id'], $user['role']);
        $barangayNames = $this->getBarangayNamesForReports($pendingReportsCollection->all());

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

        if (!$barangayId) {
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

        if (!$barangayId) {
            return $this->view('reports.verified', [
                'verifiedReports' => [],
                'barangayNames' => []
            ])->with('warning', 'Unable to determine barangay. Showing empty reports.');
        }

        $verifiedReports = $this->getVerifiedReports($barangayId);
        $barangayNames = $this->getBarangayNamesForReports($verifiedReports);

        return $this->view('reports.verified', compact('verifiedReports', 'barangayNames'));
    }

    public function exportVerifiedCsv(Request $request)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to export verified reports.');
        }

        $barangayId = $this->getBarangayId();
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Unable to determine barangay. Please contact your administrator.');
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $symptomFilter = strtolower(trim((string) $request->input('symptom', '')));
        $verifiedByFilter = strtolower(trim((string) $request->input('verified_by', '')));

        $reports = collect($this->getVerifiedReports($barangayId))->filter(function ($report) use ($dateFrom, $dateTo, $symptomFilter, $verifiedByFilter) {
            if (!empty($dateFrom)) {
                try {
                    $verifiedDate = Carbon::parse($report['verified_at'] ?? $report['createdAt'] ?? null);
                    if ($verifiedDate->lt(Carbon::parse($dateFrom)->startOfDay())) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            if (!empty($dateTo)) {
                try {
                    $verifiedDate = Carbon::parse($report['verified_at'] ?? $report['createdAt'] ?? null);
                    if ($verifiedDate->gt(Carbon::parse($dateTo)->endOfDay())) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            if (!empty($symptomFilter)) {
                $symptoms = array_map('strtolower', $report['symptoms'] ?? []);
                if (!in_array($symptomFilter, $symptoms, true)) {
                    return false;
                }
            }

            if (!empty($verifiedByFilter)) {
                $verifiedBy = strtolower((string) ($report['verified_by'] ?? ''));
                if (!str_contains($verifiedBy, $verifiedByFilter)) {
                    return false;
                }
            }

            return true;
        })->values();

        $barangayNames = $this->getBarangayNamesForReports($reports->all());
        $filename = 'verified-reports-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($reports, $barangayNames) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Report ID',
                'Barangay',
                'Symptoms',
                'Affected Person',
                'Start Date',
                'Verified Date',
                'Verified By',
                'Additional Info',
                'Status',
            ]);

            foreach ($reports as $report) {
                $symptoms = $report['symptoms'] ?? [];
                if (!is_array($symptoms)) {
                    $symptoms = [];
                }

                $additionalInfo = $report['additionalInfo'] ?? '';
                if (is_array($additionalInfo)) {
                    $additionalInfo = json_encode($additionalInfo);
                }

                fputcsv($handle, [
                    $report['id'] ?? '',
                    $barangayNames[$report['barangayId'] ?? ''] ?? 'Unknown',
                    implode(', ', array_map('ucfirst', $symptoms)),
                    $report['affectedPerson'] ?? '',
                    $report['startDate'] ?? '',
                    $report['verified_at'] ?? '',
                    $report['verified_by'] ?? '',
                    $additionalInfo,
                    $report['status'] ?? 'verified',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
            $verifierName = $request->input('verified_by');

            if (!$verifierName) {
                return redirect()->back()->with('error', 'Please select a health worker who verified this report.');
            }

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
                return null;
            }

            if ($barangayId && in_array($userRole, ['barangay', 'rhu'])) {
                $firebaseUid = $user['uid'] ?? $user['firebase_uid'] ?? null;

                if ($firebaseUid) {
                    $accounts = $this->firestore
                        ->collection($userRole)
                        ->document($barangayId)
                        ->collection('accounts')
                        ->where('uid', '=', $firebaseUid)
                        ->documents();

                    foreach ($accounts as $account) {
                        if ($account->exists()) {
                            $name = $account->data()['name'] ?? null;
                            if ($name) return $name;
                        }
                    }
                }

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
                            $name = $account->data()['name'] ?? null;
                            if ($name) return $name;
                        }
                    }
                }
            }

            $userDoc = $this->firestore
                ->collection($userRole)
                ->document($userId)
                ->snapshot();

            if ($userDoc->exists()) {
                $data = $userDoc->data();
                $name = $data['healthCenterName'] ?? $data['name'] ?? $data['barangay'] ?? null;
                if ($name) return $name;
            }

            return $user['name'] ?? null;
        } catch (\Exception $e) {
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
            return redirect()->back()->with('error', 'Failed to reject report: ' . $e->getMessage());
        }
    }

    public function resolve(Request $request, $id)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->back()->with('error', 'Please login to resolve reports.');
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
            if (($reportData['barangayId'] ?? null) !== $barangayId) {
                return redirect()->back()->with('error', 'You can only resolve reports from your barangay.');
            }

            if (($reportData['status'] ?? null) !== 'verified') {
                return redirect()->back()->with('error', 'Only verified reports can be marked as resolved.');
            }

            $this->firestore
                ->collection("reports")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'resolved'],
                    ['path' => 'resolved_at', 'value' => now()->toDateTimeString()],
                    ['path' => 'resolved_by', 'value' => session('user.name', 'Health Worker')],
                    ['path' => 'resolved_by_id', 'value' => session('user.id')],
                ]);

            return redirect()->back()->with('success', 'Report marked as resolved.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to resolve report: ' . $e->getMessage());
        }
    }

    private function getVerifiedHealthReports($barangayId, $filter, $dateRange, $symptomFilter)
    {
        $reports = [];

        if (!$barangayId) {
            return $reports;
        }

        try {
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
        } catch (\Exception $e) {
            // continue with empty reports
        }

        return $reports;
    }

    private function getAllVerifiedHealthReports($filter, $dateRange, $symptomFilter)
    {
        $reports = [];

        try {
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
                    $startDate = $endDate->copy()->subYear();
            }

            $documents = $this->firestore
                ->collection("reports")
                ->where('status', '=', 'verified')
                ->documents();

            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();

                    $dateField = $reportData['verified_at'] ?? $reportData['startDate'] ?? $reportData['createdAt'] ?? null;

                    if ($dateField) {
                        try {
                            $reportDate = Carbon::parse($dateField);
                            if (!$reportDate->between($startDate, $endDate)) {
                                continue;
                            }
                        } catch (\Exception $e) {
                            // include report if date parsing fails
                        }
                    }

                    if ($filter !== 'all' && !$this->matchesCondition($reportData, $filter)) {
                        continue;
                    }

                    if ($symptomFilter !== 'all' && !$this->hasSymptom($reportData, $symptomFilter)) {
                        continue;
                    }

                    $reports[] = array_merge($reportData, ['id' => $doc->id()]);
                }
            }
        } catch (\Exception $e) {
            // continue with empty reports
        }

        return $reports;
    }

    private function getAllResolvedHealthReports($filter, $dateRange, $symptomFilter)
    {
        $reports = [];

        try {
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
                    $startDate = $endDate->copy()->subYear();
            }

            $documents = $this->firestore
                ->collection("reports")
                ->where('status', '=', 'resolved')
                ->documents();

            foreach ($documents as $doc) {
                if (!$doc->exists()) {
                    continue;
                }

                $reportData = $doc->data();
                $dateField = $reportData['resolved_at'] ?? $reportData['verified_at'] ?? $reportData['startDate'] ?? $reportData['createdAt'] ?? null;

                if ($dateField) {
                    try {
                        $reportDate = Carbon::parse($dateField);
                        if (!$reportDate->between($startDate, $endDate)) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        // keep report if date cannot be parsed
                    }
                }

                if ($filter !== 'all' && !$this->matchesCondition($reportData, $filter)) {
                    continue;
                }

                if ($symptomFilter !== 'all' && !$this->hasSymptom($reportData, $symptomFilter)) {
                    continue;
                }

                $reports[] = array_merge($reportData, ['id' => $doc->id()]);
            }
        } catch (\Exception $e) {
            // continue with empty reports
        }

        return $reports;
    }

    private function getAllUnverifiedSymptomSignals($filter, $dateRange, $symptomFilter)
    {
        $reports = [];

        try {
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
                    $startDate = $endDate->copy()->subYear();
            }

            $documents = $this->firestore
                ->collection("reports")
                ->where('status', '=', 'to be reviewed')
                ->documents();

            foreach ($documents as $doc) {
                if (!$doc->exists()) {
                    continue;
                }

                $reportData = $doc->data();
                $dateField = $reportData['date'] ?? $reportData['createdAt'] ?? $reportData['startDate'] ?? null;
                if ($dateField) {
                    try {
                        $reportDate = Carbon::parse($dateField);
                        if (!$reportDate->between($startDate, $endDate)) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        // keep record if date cannot be parsed
                    }
                }

                if ($filter !== 'all' && !$this->matchesCondition($reportData, $filter)) {
                    continue;
                }

                if ($symptomFilter !== 'all' && !$this->hasSymptom($reportData, $symptomFilter)) {
                    continue;
                }

                $reports[] = array_merge($reportData, ['id' => $doc->id()]);
            }
        } catch (\Exception $e) {
            // continue with empty reports
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

            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $location = $data['location'] ?? null;

                    if ($location) {
                        if (is_object($location) && method_exists($location, 'latitude') && method_exists($location, 'longitude')) {
                            $barangays[$doc->id()] = [
                                'id' => $doc->id(),
                                'name' => $data['healthCenterName'] ?? $data['name'] ?? 'Unknown',
                                'lat' => $location->latitude(),
                                'lng' => $location->longitude()
                            ];
                        } elseif (is_array($location) && isset($location['latitude']) && isset($location['longitude'])) {
                            $barangays[$doc->id()] = [
                                'id' => $doc->id(),
                                'name' => $data['healthCenterName'] ?? $data['name'] ?? 'Unknown',
                                'lat' => $location['latitude'],
                                'lng' => $location['longitude']
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // continue with empty barangays
        }

        return $barangays;
    }

    private function getPendingReports($barangayId)
    {
        $pendingReports = [];

        if (!$barangayId) {
            return $pendingReports;
        }

        try {
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();

            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    $status = $reportData['status'] ?? 'unknown';

                    if ($status === 'to be reviewed') {
                        $normalized = [
                            'id' => $doc->id(),
                            'barangayId' => $barangayId,
                            'symptoms' => isset($reportData['condition']) ? [(string)$reportData['condition']] : ($reportData['symptoms'] ?? []),
                            'affectedPerson' => $reportData['reported_by'] ?? ($reportData['affectedPerson'] ?? 'Unknown'),
                            'startDate' => $reportData['date'] ?? ($reportData['startDate'] ?? null),
                            'additionalInfo' => $reportData['description'] ?? ($reportData['additionalInfo'] ?? null),
                            'createdAt' => $reportData['date'] ?? ($reportData['createdAt'] ?? null),
                            'location' => $reportData['location'] ?? null,
                            'cases' => $reportData['cases'] ?? null,
                            'status' => $status,
                        ];

                        $pendingReports[] = array_merge($reportData, $normalized);
                    }
                }
            }
        } catch (\Exception $e) {
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

            usort($staffAccounts, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $staffAccounts;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRejectedReports($barangayId)
    {
        $rejectedReports = [];

        if (!$barangayId) {
            return $rejectedReports;
        }

        try {
            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();

            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();
                    if (($reportData['status'] ?? '') === 'rejected') {
                        $rejectedReports[] = array_merge($reportData, ['id' => $doc->id()]);
                    }
                }
            }

            usort($rejectedReports, function ($a, $b) {
                $dateA = $a['rejected_at'] ?? $a['createdAt'] ?? '';
                $dateB = $b['rejected_at'] ?? $b['createdAt'] ?? '';
                return strtotime($dateB) - strtotime($dateA);
            });
        } catch (\Exception $e) {
            return [];
        }

        return $rejectedReports;
    }

    private function getVerifiedReports($barangayId)
    {
        $verifiedReports = [];

        if (!$barangayId) {
            return $verifiedReports;
        }

        try {
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

            usort($verifiedReports, function ($a, $b) {
                $dateA = $a['verified_at'] ?? $a['createdAt'] ?? '';
                $dateB = $b['verified_at'] ?? $b['createdAt'] ?? '';
                return strtotime($dateB) - strtotime($dateA);
            });
        } catch (\Exception $e) {
            return [];
        }

        return $verifiedReports;
    }

    private function getRejectedStats($barangayId)
    {
        $stats = [
            'total_rejected' => 0,
            'rejected_today' => 0,
            'rejected_this_month' => 0
        ];

        if (!$barangayId) {
            return $stats;
        }

        try {
            $today = Carbon::today();

            $allDocs = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->documents();

            foreach ($allDocs as $doc) {
                if ($doc->exists()) {
                    $reportData = $doc->data();

                    if (($reportData['status'] ?? '') === 'rejected') {
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
        } catch (\Exception $e) {
            // continue with zero stats
        }

        return $stats;
    }

    private function getVerificationStats($barangayId)
    {
        $stats = [
            'pending' => 0,
            'verified_today' => 0,
            'rejected_today' => 0,
            'total_this_month' => 0
        ];

        if (!$barangayId) {
            return $stats;
        }

        try {
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

                    if ($status === 'to be reviewed') {
                        $stats['pending']++;
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
        } catch (\Exception $e) {
            // continue with zero stats
        }

        return $stats;
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
            if (!$barangayId) {
                return ['Fever', 'Dengue', 'Diarrhea', 'Cough', 'Headache'];
            }

            $documents = $this->firestore
                ->collection("reports")
                ->where('barangayId', '=', $barangayId)
                ->where('status', '=', 'verified')
                ->limit(100)
                ->documents();

            $symptoms = [];
            foreach ($documents as $doc) {
                if ($doc->exists()) {
                    $reportSymptoms = $doc->data()['symptoms'] ?? [];

                    if (is_array($reportSymptoms)) {
                        foreach ($reportSymptoms as $symptom) {
                            $symptoms[strtolower($symptom)] = ucfirst($symptom);
                        }
                    }
                }
            }

            return array_values($symptoms);
        } catch (\Exception $e) {
            return ['Fever', 'Dengue', 'Diarrhea', 'Cough', 'Headache'];
        }
    }

    private function processHeatmapData($reports, $barangays)
    {
        $heatmapData = [];
        $barangayStats = [];

        foreach ($reports as $report) {
            $barangayId = $report['barangayId'] ?? null;

            if (!$barangayId || !isset($barangays[$barangayId])) {
                continue;
            }

            if (!isset($barangayStats[$barangayId])) {
                $barangayStats[$barangayId] = [
                    'cases' => 0,
                    'symptoms' => []
                ];
            }

            $barangayStats[$barangayId]['cases']++;

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
            // continue
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

    private function processVerifiedBubbleData(array $activeReports, array $resolvedReports, array $barangays): array
    {
        $grouped = [];
        foreach ($activeReports as $report) {
            $barangayId = $report['barangayId'] ?? null;
            if (!$barangayId || !isset($barangays[$barangayId])) {
                continue;
            }

            $category = $this->categorizeConfirmedDisease($report);
            if (!isset($grouped[$barangayId])) {
                $grouped[$barangayId] = [
                    'barangayId' => $barangayId,
                    'barangay' => $barangays[$barangayId]['name'],
                    'lat' => $barangays[$barangayId]['lat'],
                    'lng' => $barangays[$barangayId]['lng'],
                    'activeCases' => 0,
                    'resolvedCases' => 0,
                    'totalConfirmedCases' => 0,
                    'categories' => [
                        'dengue' => ['active' => 0, 'resolved' => 0, 'symptoms' => []],
                        'respiratory' => ['active' => 0, 'resolved' => 0, 'symptoms' => []],
                        'waterborne' => ['active' => 0, 'resolved' => 0, 'symptoms' => []],
                    ],
                ];
            }

            $grouped[$barangayId]['activeCases']++;
            $grouped[$barangayId]['totalConfirmedCases']++;
            $grouped[$barangayId]['categories'][$category]['active']++;
            $grouped[$barangayId]['categories'][$category]['symptoms'] = array_values(array_unique(array_merge(
                $grouped[$barangayId]['categories'][$category]['symptoms'],
                $this->extractSymptoms($report)
            )));
        }

        foreach ($resolvedReports as $report) {
            $barangayId = $report['barangayId'] ?? null;
            if (!$barangayId || !isset($barangays[$barangayId])) {
                continue;
            }

            $category = $this->categorizeConfirmedDisease($report);
            if (!isset($grouped[$barangayId])) {
                $grouped[$barangayId] = [
                    'barangayId' => $barangayId,
                    'barangay' => $barangays[$barangayId]['name'],
                    'lat' => $barangays[$barangayId]['lat'],
                    'lng' => $barangays[$barangayId]['lng'],
                    'activeCases' => 0,
                    'resolvedCases' => 0,
                    'totalConfirmedCases' => 0,
                    'categories' => [
                        'dengue' => ['active' => 0, 'resolved' => 0, 'symptoms' => []],
                        'respiratory' => ['active' => 0, 'resolved' => 0, 'symptoms' => []],
                        'waterborne' => ['active' => 0, 'resolved' => 0, 'symptoms' => []],
                    ],
                ];
            }

            $grouped[$barangayId]['resolvedCases']++;
            $grouped[$barangayId]['totalConfirmedCases']++;
            $grouped[$barangayId]['categories'][$category]['resolved']++;
            $grouped[$barangayId]['categories'][$category]['symptoms'] = array_values(array_unique(array_merge(
                $grouped[$barangayId]['categories'][$category]['symptoms'],
                $this->extractSymptoms($report)
            )));
        }

        $bubbles = [];
        foreach ($grouped as $entry) {
            foreach ($entry['categories'] as $category => $counts) {
                $activeCount = $counts['active'] ?? 0;
                $resolvedCount = $counts['resolved'] ?? 0;
                $confirmedCount = $activeCount + $resolvedCount;

                if ($confirmedCount <= 0) {
                    continue;
                }

                $bubbles[] = [
                    'barangayId' => $entry['barangayId'],
                    'barangay' => $entry['barangay'],
                    'lat' => $entry['lat'],
                    'lng' => $entry['lng'],
                    'totalCases' => $activeCount,
                    'resolvedCases' => $resolvedCount,
                    'confirmedTotalCases' => $confirmedCount,
                    'barangayTotalCases' => $entry['activeCases'],
                    'barangayResolvedCases' => $entry['resolvedCases'],
                    'barangayConfirmedTotalCases' => $entry['totalConfirmedCases'],
                    'diseaseCategory' => $category,
                    'symptoms' => $counts['symptoms'] ?? [],
                    'categories' => $entry['categories'],
                ];
            }
        }

        return array_values($bubbles);
    }

    private function processUnverifiedBubbleData(array $reports, array $barangays): array
    {
        $grouped = [];
        foreach ($reports as $report) {
            $barangayId = $report['barangayId'] ?? null;
            if (!$barangayId || !isset($barangays[$barangayId])) {
                continue;
            }

            $category = $this->categorizeUnverifiedSignal($report);
            if (!isset($grouped[$barangayId])) {
                $grouped[$barangayId] = [
                    'barangayId' => $barangayId,
                    'barangay' => $barangays[$barangayId]['name'],
                    'lat' => $barangays[$barangayId]['lat'],
                    'lng' => $barangays[$barangayId]['lng'],
                    'totalSignals' => 0,
                    'categories' => [
                        'dengue' => 0,
                        'respiratory' => 0,
                        'waterborne' => 0,
                    ],
                ];
            }

            $grouped[$barangayId]['totalSignals']++;
            $grouped[$barangayId]['categories'][$category]++;
        }

        $bubbles = [];
        foreach ($grouped as $entry) {
            arsort($entry['categories']);
            $dominant = array_key_first($entry['categories']);
            $bubbles[] = array_merge($entry, [
                'possibleCategory' => $dominant,
                'dominantSignals' => $entry['categories'][$dominant] ?? 0,
            ]);
        }

        return array_values($bubbles);
    }

    private function buildHotspotData(array $verifiedBubbleData): array
    {
        $hotspots = [];
        $distanceThresholdKm = 1.5;
        $used = [];

        for ($i = 0; $i < count($verifiedBubbleData); $i++) {
            if (isset($used[$i])) {
                continue;
            }

            $base = $verifiedBubbleData[$i];
            $cluster = [$base];
            for ($j = $i + 1; $j < count($verifiedBubbleData); $j++) {
                if (isset($used[$j])) {
                    continue;
                }

                $target = $verifiedBubbleData[$j];
                if (($target['diseaseCategory'] ?? null) !== ($base['diseaseCategory'] ?? null)) {
                    continue;
                }

                $distance = $this->distanceKm(
                    $base['lat'] ?? 0,
                    $base['lng'] ?? 0,
                    $target['lat'] ?? 0,
                    $target['lng'] ?? 0
                );

                if ($distance <= $distanceThresholdKm) {
                    $cluster[] = $target;
                    $used[$j] = true;
                }
            }

            if (count($cluster) < 2) {
                continue;
            }

            $count = count($cluster);
            $lat = array_sum(array_column($cluster, 'lat')) / $count;
            $lng = array_sum(array_column($cluster, 'lng')) / $count;
            $cases = array_sum(array_column($cluster, 'totalCases'));

            $hotspots[] = [
                'lat' => $lat,
                'lng' => $lng,
                'radius' => 350 + ($cases * 35),
                'diseaseCategory' => $base['diseaseCategory'],
                'barangayCount' => $count,
                'totalCases' => $cases,
            ];
        }

        return $hotspots;
    }

    private function distanceKm($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function categorizeConfirmedDisease(array $report): string
    {
        $disease = strtolower((string) ($report['confirmed_disease'] ?? $report['disease'] ?? $report['condition'] ?? ''));
        $symptoms = array_map('strtolower', $report['symptoms'] ?? []);
        $tokens = trim($disease . ' ' . implode(' ', $symptoms));

        if (str_contains($tokens, 'dengue')) {
            return 'dengue';
        }
        if (str_contains($tokens, 'cholera') || str_contains($tokens, 'diarrhea')) {
            return 'waterborne';
        }
        if (
            str_contains($tokens, 'covid') ||
            str_contains($tokens, 'influenza') ||
            str_contains($tokens, 'respiratory') ||
            str_contains($tokens, 'cough')
        ) {
            return 'respiratory';
        }

        return 'respiratory';
    }

    private function categorizeUnverifiedSignal(array $report): string
    {
        $symptoms = array_map('strtolower', $report['symptoms'] ?? []);
        $condition = strtolower((string) ($report['condition'] ?? ''));
        $tokens = trim($condition . ' ' . implode(' ', $symptoms));

        if (str_contains($tokens, 'dengue') || str_contains($tokens, 'fever') || str_contains($tokens, 'rash')) {
            return 'dengue';
        }
        if (str_contains($tokens, 'diarrhea') || str_contains($tokens, 'cholera')) {
            return 'waterborne';
        }
        if (str_contains($tokens, 'cough') || str_contains($tokens, 'flu') || str_contains($tokens, 'covid')) {
            return 'respiratory';
        }

        return 'respiratory';
    }

    private function extractSymptoms(array $report): array
    {
        $symptoms = $report['symptoms'] ?? [];
        if (!is_array($symptoms)) {
            $symptoms = [];
        }

        if (empty($symptoms) && !empty($report['condition'])) {
            $symptoms[] = (string) $report['condition'];
        }

        return array_values(array_filter(array_map(function ($symptom) {
            return strtolower(trim((string) $symptom));
        }, $symptoms)));
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
