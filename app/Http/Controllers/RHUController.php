<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Http;
use App\Services\LocationService; // add
use Carbon\Carbon;

class RHUController extends Controller
{
    public function index(FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        // Get RHU details
        $rhuDoc = $firestore->db->collection('rhu')->document($currentRhuId)->snapshot();
        $rhuName = 'Rural Health Unit';
        if ($rhuDoc->exists()) {
            $rhuData = $rhuDoc->data();
            $rhuName = $rhuData['name'] ?? 'Rural Health Unit';
        }
        
        $bhuQuery = $firestore->db->collection('barangay')
            ->where('rhuId', '=', $currentRhuId)
            ->where('status', '=', 'approved')
            ->documents();
        
        $barangayHealthUnits = [];
        foreach ($bhuQuery as $document) {
            if ($document->exists()) {
                $barangayHealthUnits[] = array_merge(['id' => $document->id()], $document->data());
            }
        }
        
        return view('rhus.index', compact('barangayHealthUnits', 'rhuName'));
    }

    public function indexApprovals(FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        $bhuQuery = $firestore->db->collection('barangay')
            ->where('rhuId', '=', $currentRhuId)
            ->where('status', '=', 'pending')
            ->documents();
        
        $barangayHealthUnits = [];
        foreach ($bhuQuery as $document) {
            if ($document->exists()) {
                $barangayHealthUnits[] = array_merge(['id' => $document->id()], $document->data());
            }
        }
        
        return view('rhus.indexApprovals', compact('barangayHealthUnits'));
    }

    public function indexDoctors(FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        // Get all BHU IDs for this RHU first
        $bhuQuery = $firestore->db->collection('barangay')
            ->where('rhuId', '=', $currentRhuId)
            ->where('status', '=', 'approved')
            ->documents();
        
        $bhuIds = [];
        foreach ($bhuQuery as $bhuDoc) {
            if ($bhuDoc->exists()) {
                $bhuIds[] = $bhuDoc->id();
            }
        }
        
        $doctors = [];
        
        if (!empty($bhuIds)) {
            // Get doctors assigned to these BHUs
            foreach ($bhuIds as $bhuId) {
                $doctorQuery = $firestore->db->collection('health_worker')
                    ->where('barangayId', '=', $bhuId)
                    ->documents();
                
                foreach ($doctorQuery as $document) {
                    if ($document->exists()) {
                        $data = $document->data();
                        if (($data['type'] ?? '') === 'Doctor') {
                            $doctors[] = array_merge(['id' => $document->id()], $data);
                        }
                    }
                }
            }
        }
        
        return view('rhus.indexDoctors', compact('doctors'));
    }

    public function indexReports(Request $request, FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Read filters
        $filters = [
            'status'   => strtolower(trim($request->query('status', ''))),
            'barangay' => $request->query('barangay', ''),
            'from'     => $request->query('from', ''),
            'to'       => $request->query('to', ''),
        ];
        $fromDate = $filters['from'] ? Carbon::parse($filters['from'])->startOfDay() : null;
        $toDate   = $filters['to']   ? Carbon::parse($filters['to'])->endOfDay()   : null;

        // Get BHU IDs owned by this RHU
        $bhuIds = [];
        $barangayOptions = [];
        try {
            $bhuQuery = $firestore->db->collection('barangay')
                ->where('rhuId', '=', $currentRhuId)
                ->documents();
            foreach ($bhuQuery as $bhuDoc) {
                if ($bhuDoc->exists()) {
                    $bhuIds[] = $bhuDoc->id();
                    $bData = $bhuDoc->data();
                    $barangayOptions[$bhuDoc->id()] = $bData['barangay'] ?? ($bData['healthCenterName'] ?? $bhuDoc->id());
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed fetching BHUs for reports: '.$e->getMessage());
        }

        $reports = [];
        $statusCounts = ['to be reviewed' => 0, 'reviewed' => 0, 'other' => 0];
        $symptomCounts = [];

        // Last 30 days trend seed
        $trendSeed = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i)->format('Y-m-d');
            $trendSeed[$d] = 0;
        }

        if (!empty($bhuIds)) {
            try {
                // Scan all reports (optimize later if you add composite indexes)
                $allReports = $firestore->db->collection('reports')->documents();
                foreach ($allReports as $doc) {
                    if (!$doc->exists()) continue;
                    $data = $doc->data();
                    $barangayId = $data['barangayId'] ?? null;
                    if (!$barangayId || !in_array($barangayId, $bhuIds, true)) continue;

                    // Build a normalized record
                    $createdAt = isset($data['createdAt']) ? Carbon::parse($data['createdAt']) : null;

                    // Apply filters
                    if ($filters['status'] !== '' && strtolower($data['status'] ?? '') !== $filters['status']) continue;
                    if ($filters['barangay'] !== '' && $barangayId !== $filters['barangay']) continue;
                    if ($fromDate && (!$createdAt || $createdAt->lt($fromDate))) continue;
                    if ($toDate && (!$createdAt || $createdAt->gt($toDate))) continue;

                    $row = array_merge(['id' => $doc->id()], $data, [
                        'barangayName' => $barangayOptions[$barangayId] ?? $barangayId,
                        'createdAt'    => $createdAt ? $createdAt->toDateTimeString() : ($data['createdAt'] ?? ''),
                    ]);
                    $reports[] = $row;

                    // Aggregate status
                    $st = strtolower($data['status'] ?? '');
                    if ($st === 'to be reviewed') $statusCounts['to be reviewed']++;
                    elseif ($st === 'reviewed')   $statusCounts['reviewed']++;
                    else                           $statusCounts['other']++;

                    // Aggregate symptoms
                    if (!empty($data['symptoms']) && is_array($data['symptoms'])) {
                        foreach ($data['symptoms'] as $s) {
                            $symptomCounts[$s] = ($symptomCounts[$s] ?? 0) + 1;
                        }
                    }

                    // Aggregate trend (last 30 days by createdAt date)
                    if ($createdAt) {
                        $key = $createdAt->format('Y-m-d');
                        if (array_key_exists($key, $trendSeed)) {
                            $trendSeed[$key]++;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed fetching reports: '.$e->getMessage());
            }
        }

        // Sort newest first
        usort($reports, fn($a, $b) => strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? ''));

        // Prepare view data
        arsort($symptomCounts);
        $summary = [
            'total'        => count($reports),
            'toBeReviewed' => $statusCounts['to be reviewed'],
            'reviewed'     => $statusCounts['reviewed'],
            'other'        => $statusCounts['other'],
        ];
        $trend = [
            'labels' => array_keys($trendSeed),
            'data'   => array_values($trendSeed),
        ];

        return view('rhus.indexReports', compact(
            'reports', 'summary', 'symptomCounts', 'trend', 'barangayOptions', 'filters'
        ));
    }

    public function showReport($id, FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }
        $report = null;
        try {
            $docRef = $firestore->db->collection('reports')->document($id);
            $doc = $docRef->snapshot();
            if ($doc->exists()) {
                $data = $doc->data();
                if (!empty($data['barangayId'])) {
                    $barangayDoc = $firestore->db->collection('barangay')->document($data['barangayId'])->snapshot();
                    if ($barangayDoc->exists() && ($barangayDoc->data()['rhuId'] ?? null) === $currentRhuId) {
                        // If status is 'to be reviewed', update to 'reviewed'
                        if (strtolower($data['status'] ?? '') === 'to be reviewed') {
                            $docRef->update([
                                ['path' => 'status', 'value' => 'reviewed'],
                                ['path' => 'updated_at', 'value' => now()->toDateTimeString()]
                            ]);
                            $data['status'] = 'reviewed';
                        }
                        $barangayName = $barangayDoc->data()['barangay'] ?? ($barangayDoc->data()['healthCenterName'] ?? $data['barangayId']);
                        $report = array_merge(['id' => $doc->id()], $data, ['barangayName' => $barangayName]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed fetching report: '.$e->getMessage());
        }
        if (!$report) {
            return redirect()->route('rhu.reports')->with('error', 'Report not found or access denied.');
        }
        return view('rhus.viewReport', compact('report'));
    }

    // Alternative approach if notifications don't have rhuId directly
    public function indexNotifications(FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        
        // Get all BHU IDs for this RHU first
        $bhuQuery = $firestore->db->collection('barangay')
            ->where('rhuId', '=', $currentRhuId)
            ->documents();
        
        $bhuIds = [];
        foreach ($bhuQuery as $bhuDoc) {
            if ($bhuDoc->exists()) {
                $bhuIds[] = $bhuDoc->id();
            }
        }
        
        $notifications = [];
        
        if (!empty($bhuIds)) {
            // Get notifications related to these BHUs
            foreach ($bhuIds as $bhuId) {
                $notificationQuery = $firestore->db->collection('notifications')
                    ->where('barangayId', '=', $bhuId)
                    ->documents();
                
                foreach ($notificationQuery as $document) {
                    if ($document->exists()) {
                        $notifications[] = array_merge(['id' => $document->id()], $document->data());
                    }
                }
            }
            
            // Sort by created_at descending
            usort($notifications, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }
        
        return view('rhus.indexNotifications', compact('notifications'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request, FirestoreService $firestore)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'contactInfo' => 'nullable|string|max:255',
            'fullAddress' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'userId' => 'nullable|string|max:255',
        ]);

        $firestore->addDocument('rhu', [
            'name' => $validated['name'],
            'city' => $validated['city'],
            'contactInfo' => $validated['contactInfo'] ?? '',
            'createdAt' => now()->toDateTimeString(),
            'fullAddress' => $validated['fullAddress'] ?? '',
            'location' => $validated['location'] ?? '',
            'region' => $validated['region'] ?? '',
            'status' => 'pending',
            'userId' => $validated['userId'] ?? '',
            'approvedBy' => '',
        ]);

        return redirect()->route('rural-health-units.create')
            ->with('success', 'Application submitted successfully!');
    }

    public function show($id, FirestoreService $firestore, LocationService $location)
    {
        $doc = $firestore->db->collection('barangay')->document($id)->snapshot();
        if (!$doc->exists()) {
            abort(404, 'BHU not found');
        }

        $barangayHealthUnit = array_merge(['id' => $doc->id()], $doc->data());

        $geo = $location->convertCodesToNames([
            'region'   => $barangayHealthUnit['region']   ?? null,
            'province' => $barangayHealthUnit['province'] ?? null,
            'city'     => $barangayHealthUnit['city']     ?? null,
        ]);

        $barangayName = $barangayHealthUnit['barangay'] ?? '';
        $cityName     = $geo['cityName'] ?? '';
        $regionName   = $geo['regionName'] ?? '';
        $provinceName = $geo['provinceName'] ?? '';

        return view('rhus.show', compact(
            'barangayHealthUnit', 'barangayName', 'cityName', 'regionName', 'provinceName'
        ));
    }

    public function showPending(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('rhu');
        $ruralHealthUnits = [];
        foreach ($documents as $document) {
            $data = $document->data();
            if (($data['status'] ?? '') === 'pending') {
                $ruralHealthUnits[] = array_merge(['id' => $document->id()], $data);
            }
        }
        return view('admin.show', compact('ruralHealthUnits'));
    }

    public function edit($id, FirestoreService $firestore)
    {
        $document = $firestore->db->collection('barangay')->document($id)->snapshot();
        
        if (!$document->exists()) {
            abort(404, 'Barangay Health Unit not found');
        }
        
        $barangayHealthUnit = array_merge(['id' => $document->id()], $document->data());
        
        return view('rhus.edit', compact('barangayHealthUnit'));
    }

    public function update(Request $request, $id, FirestoreService $firestore)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending'
        ]);
        
        $firestore->db->collection('barangay')->document($id)->update([
            ['path' => 'status', 'value' => $validated['status']],
            ['path' => 'updated_at', 'value' => now()->toDateTimeString()]
        ]);
        
        $message = $validated['status'] === 'approved' ? 'BHU approved successfully!' : 'BHU rejected successfully!';
        
        return redirect()->route('rhu.approvals')->with('success', $message);
    }

    public function destroy($id, FirestoreService $firestore)
    {
        $firestore->db->collection('rhu')->document($id)->delete();
        return back()->with('success', 'RHU deleted successfully!');
    }

    private function getCityName($cityCode)
    {
        if (!$cityCode) return '';
        $response = Http::get("https://psgc.gitlab.io/api/cities/{$cityCode}");
        if ($response->successful()) {
            return $response->json('name');
        }
        return $cityCode; 
    }

    private function getBarangayName($barangayCode)
    {
        if (!$barangayCode) return '';
        
        $response = Http::get("https://psgc.gitlab.io/api/barangays/{$barangayCode}");
        if ($response->successful()) {
            return $response->json('name');
        }
        return $barangayCode;
    }
    
    public function pending()
    {
        $user = session('user');
        $rhuData = $user['rhuData'] ?? [];
        
        // Check if status changed to approved
        if (($rhuData['status'] ?? '') === 'approved') {
            return redirect()->route('BHUs.index')->with('success', 'Welcome! Your account has been approved.');
        }
        
        return view('rhus.pending', compact('rhuData'));
    }
}