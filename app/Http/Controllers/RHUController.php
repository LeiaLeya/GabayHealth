<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Http;

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

    public function indexReports(FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Get BHU (barangay health unit) IDs owned by this RHU
        $bhuIds = [];
        try {
            $bhuQuery = $firestore->db->collection('barangay')
                ->where('rhuId', '=', $currentRhuId)
                ->documents();
            foreach ($bhuQuery as $bhuDoc) {
                if ($bhuDoc->exists()) {
                    $bhuIds[] = $bhuDoc->id();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed fetching BHUs for reports: '.$e->getMessage());
        }

        $reports = [];
        if (!empty($bhuIds)) {
            // Preload barangay names for mapping (avoid repeated calls)
            $barangayNameMap = [];
            try {
                foreach ($bhuIds as $bid) {
                    $bDoc = $firestore->db->collection('barangay')->document($bid)->snapshot();
                    if ($bDoc->exists()) {
                        $bData = $bDoc->data();
                        $barangayNameMap[$bid] = $bData['barangayName'] ?? $bData['barangay'] ?? ($bData['healthCenterName'] ?? $bid);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed preloading barangay names: '.$e->getMessage());
            }

            try {
                // Firestore PHP SDK has no native whereIn pre-aggregation in this code; brute force scan
                $allReports = $firestore->db->collection('reports')->documents();
                foreach ($allReports as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $barangayId = $data['barangayId'] ?? null;
                        if (in_array($barangayId, $bhuIds, true)) {
                            if ($barangayId && isset($barangayNameMap[$barangayId])) {
                                $data['barangayName'] = $barangayNameMap[$barangayId];
                            }
                            $reports[] = array_merge(['id' => $doc->id()], $data);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed fetching reports: '.$e->getMessage());
            }
        }

        // Sort newest first using createdAt ISO string
        usort($reports, function ($a, $b) {
            return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
        });

        return view('rhus.indexReports', compact('reports'));
    }

    public function showReport($id, FirestoreService $firestore)
    {
        $currentRhuId = session('user.id');
        if (!$currentRhuId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }
        $report = null;
        try {
            $doc = $firestore->db->collection('reports')->document($id)->snapshot();
            if ($doc->exists()) {
                $data = $doc->data();
                // Validate ownership via barangay -> rhu mapping
                if (!empty($data['barangayId'])) {
                    $barangayDoc = $firestore->db->collection('barangay')->document($data['barangayId'])->snapshot();
                    if ($barangayDoc->exists() && ($barangayDoc->data()['rhuId'] ?? null) === $currentRhuId) {
                        $report = array_merge(['id' => $doc->id()], $data);
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

    public function show($id, FirestoreService $firestore)
    {
        $document = $firestore->db->collection('barangay')->document($id)->snapshot();
        
        if (!$document->exists()) {
            abort(404, 'Barangay Health Unit not found');
        }
        
        $barangayHealthUnit = array_merge(['id' => $document->id()], $document->data());
        
        $barangayName = '';
        if (isset($barangayHealthUnit['barangay'])) {
            $barangayName = $this->getBarangayName($barangayHealthUnit['barangay']);
        }
        
        $cityName = '';
        if (isset($barangayHealthUnit['city'])) {
            $cityName = $this->getCityName($barangayHealthUnit['city']);
        }
        
        $healthWorkersQuery = $firestore->db->collection('health_worker')
            ->where('barangayId', '=', $id)
            ->documents();
        
        $healthWorkers = [];
        foreach ($healthWorkersQuery as $workerDoc) {
            if ($workerDoc->exists()) {
                $healthWorkers[] = array_merge(['id' => $workerDoc->id()], $workerDoc->data());
            }
        }
        
        return view('rhus.show', compact('barangayHealthUnit', 'barangayName', 'cityName', 'healthWorkers'));
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