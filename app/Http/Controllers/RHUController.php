<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Http;

class RHUController extends Controller
{
    public function index(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('barangay');
        $barangayHealthUnits = [];
        foreach ($documents as $document) {
            $data = $document->data();
            if (($data['status'] ?? '') === 'approved') {
                $barangayHealthUnits[] = array_merge(['id' => $document->id()], $data);
            }
        }
        return view('rhus.index', compact('barangayHealthUnits'));
    }

    public function indexApprovals(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('barangay');
        $barangayHealthUnits = [];
        foreach ($documents as $document) {
            $data = $document->data();
            if (($data['status'] ?? '') === 'pending') {
                $barangayHealthUnits[] = array_merge(['id' => $document->id()], $data);
            }
        }
        return view('rhus.indexApprovals', compact('barangayHealthUnits'));
    }

    public function indexDoctors(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('health_worker');
        $doctors = [];
        foreach ($documents as $document) {
            $data = $document->data();
            if (($data['type'] ?? '') === 'Doctor') {
                $doctors[] = array_merge(['id' => $document->id()], $data);
            }
        }
        return view('rhus.indexDoctors', compact('doctors'));
    }

    public function indexNotifications(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('notifications');
        $notifications = [];
        foreach ($documents as $document) {
            $data = $document->data();
            $notifications[] = array_merge(['id' => $document->id()], $data);
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
}