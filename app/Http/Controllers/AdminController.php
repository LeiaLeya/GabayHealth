<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    // List all approved and pending RHUs
    public function index(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('rhu');
        $ruralHealthUnits = [];
        foreach ($documents as $document) {
            $data = $document->data();
            $status = $data['status'] ?? 'pending';
            if ($status === 'approved' || $status === 'pending') {
                // Resolve location names from PSGC codes
                if (isset($data['region']) && isset($data['province']) && isset($data['city'])) {
                    $location = $this->getLocationFromPSGC($data['region'], $data['province'], $data['city']);
                    if ($location) {
                        $data['displayLocation'] = $location;
                    }
                }
                $ruralHealthUnits[] = array_merge(['id' => $document->id()], $data);
            }
        }
        // Add pagination
        $page = request()->get('page', 1);
        $perPage = 8;
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($ruralHealthUnits, $offset, $perPage);
        $ruralHealthUnits = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            count($ruralHealthUnits),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        return view('admin.index', compact('ruralHealthUnits'));
    }

    // Helper to get location name with province and region from PSGC codes
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

    // List all pending RHUs for approval
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

    // Show create form
    public function create()
    {
        return view('admin.create');
    }

    // Store a new RHU in Firestore
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

    // Show edit form for a specific RHU
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

    // Update an RHU (e.g., approve)
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

    // Delete an RHU
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
        return $cityCode; // fallback if not found
    }
}