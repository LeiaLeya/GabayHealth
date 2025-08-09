<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Http;
use App\Services\LocationService;

class AdminController extends Controller
{

    public function index(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('rhu');
        $ruralHealthUnits = [];
        foreach ($documents as $document) {
            $data = $document->data();
            if (($data['status'] ?? '') === 'approved') {
                $ruralHealthUnits[] = array_merge(['id' => $document->id()], $data);
            }
        }
        return view('admin.index', compact('ruralHealthUnits'));
    }


    public function indexApprovals(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('rhu');
        $ruralHealthUnits = [];
        foreach ($documents as $document) {
            $data = $document->data();
            if (($data['status'] ?? '') === 'pending') {
                $ruralHealthUnits[] = array_merge(['id' => $document->id()], $data);
            }
        }
        return view('admin.indexApprovals', compact('ruralHealthUnits'));
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
        $document = $firestore->db->collection('rhu')->document($id)->snapshot();
        if (!$document->exists()) {
            abort(404);
        }
        $ruralHealthUnit = array_merge(['id' => $document->id()], $document->data());
        $cityName = $this->getCityName($ruralHealthUnit['city'] ?? '');

        $bhuQuery = $firestore->db->collection('barangay')->where('rhuId', '=', $id)->documents();
        $bhus = [];
        foreach ($bhuQuery as $bhuDoc) {
            if ($bhuDoc->exists()) {
                $data = $bhuDoc->data();
                if (($data['status'] ?? '') === 'approved') {
                    $bhus[] = array_merge(['id' => $bhuDoc->id()], $data);
                }
            }
        }

        return view('admin.show', compact('ruralHealthUnit', 'cityName', 'bhus'));
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
        $document = $firestore->db->collection('rhu')->document($id)->snapshot();
        if (!$document->exists()) {
            abort(404);
        }
        $ruralHealthUnit = array_merge(['id' => $document->id()], $document->data());
        $cityName = $this->getCityName($ruralHealthUnit['city'] ?? '');
        return view('admin.edit', compact('ruralHealthUnit', 'cityName'));
    }


    public function update(Request $request, $id, FirestoreService $firestore)
    {
        $document = $firestore->db->collection('rhu')->document($id);
        $data = [];
        if ($request->has('status')) {
            $data['status'] = $request->input('status');
        }
        if (!empty($data)) {
            $document->update([
                ['path' => 'status', 'value' => $data['status']]
            ]);
            return back()->with('success', 'RHU updated successfully!');
        }
        return back()->with('error', 'No status provided.');
    }


    public function destroy($id, FirestoreService $firestore)
    {
        $firestore->db->collection('rhu')->document($id)->delete();
        return back()->with('success', 'RHU deleted successfully!');
    }

    public function showBHU($rhuId, $bhuId, FirestoreService $firestore, LocationService $location)
    {
        // Fetch BHU
        $doc = $firestore->db->collection('barangay')->document($bhuId)->snapshot();
        if (!$doc->exists()) {
            abort(404, 'Barangay Health Unit not found');
        }
        $barangayHealthUnit = array_merge(['id' => $doc->id()], $doc->data());

        // Optional ownership check
        if (!empty($barangayHealthUnit['rhuId']) && $barangayHealthUnit['rhuId'] !== $rhuId) {
            abort(403, 'This BHU does not belong to the specified RHU.');
        }

        // Barangay name (if code)
        $barangayName = isset($barangayHealthUnit['barangay'])
            ? $this->getBarangayName($barangayHealthUnit['barangay'])
            : '';

        // Resolve region/province/city names via LocationService
        $geo = $location->convertCodesToNames([
            'region'   => $barangayHealthUnit['region']   ?? null,
            'province' => $barangayHealthUnit['province'] ?? null,
            'city'     => $barangayHealthUnit['city']     ?? null,
        ]);
        $regionName   = $geo['regionName']   ?? '';
        $provinceName = $geo['provinceName'] ?? '';
        $cityName     = $geo['cityName']     ?? '';

        // Health workers
        $healthWorkers = [];
        try {
            $workers = $firestore->db->collection('health_worker')
                ->where('barangayId', '=', $bhuId)
                ->documents();
            foreach ($workers as $w) {
                if ($w->exists()) {
                    $healthWorkers[] = array_merge(['id' => $w->id()], $w->data());
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed fetching health workers: ' . $e->getMessage());
        }

        return view('admin.bhus.show', compact(
            'barangayHealthUnit', 'barangayName', 'cityName', 'regionName', 'provinceName', 'healthWorkers', 'rhuId'
        ));
    }

    private function getCityName($cityCode)
    {
        if (!$cityCode) return '';
        $response = Http::get("https://psgc.gitlab.io/api/cities/{$cityCode}");
        if ($response->successful()) {
            return $response->json('name');
        }
        // Fallback to cities-municipalities endpoint
        $fallback = Http::get("https://psgc.gitlab.io/api/cities-municipalities/{$cityCode}");
        if ($fallback->successful()) {
            return $fallback->json('name');
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
        // If already a name (not code), just return it
        return $barangayCode;
    }
}