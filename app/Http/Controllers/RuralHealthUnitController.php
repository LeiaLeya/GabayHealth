<?php

namespace App\Http\Controllers; 

use App\Models\RuralHealthUnit;
use Illuminate\Http\Request;
use App\Services\FirebaseService;


class RuralHealthUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $firestore = app(FirebaseService::class)->getFirestore();
        $docs = $firestore->collection('rhu')->where('status', '=', 'approved')->documents();
        
        $ruralHealthUnits = collect();
        foreach ($docs as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $data['id'] = $doc->id();
                $ruralHealthUnits->push((object) $data);
            }
        }
        
        // Simple pagination for Firestore data
        $perPage = 8;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = $ruralHealthUnits->slice($offset, $perPage);
        
        $ruralHealthUnits = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $ruralHealthUnits->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        
        return view('admin.index', compact('ruralHealthUnits'));
    }

    public function indexApprovals()
    {
        $firestore = app(FirebaseService::class)->getFirestore();
        $docs = $firestore->collection('rhu')->where('status', '=', 'pending')->documents();
        
        $ruralHealthUnits = collect();
        foreach ($docs as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $data['id'] = $doc->id();
                $ruralHealthUnits->push((object) $data);
            }
        }
        
        // Simple pagination for Firestore data
        $perPage = 5;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = $ruralHealthUnits->slice($offset, $perPage);
        
        $ruralHealthUnits = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $ruralHealthUnits->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('admin.indexApprovals', compact('ruralHealthUnits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ruralHealthUnits = RuralHealthUnit::latest()->get();
        return view('admin.create', compact('ruralHealthUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        RuralHealthUnit::create([
            'name' => $validated['name'],
            'city' => $validated['city'],
            // status defaults to 'pending'
        ]);

        return redirect()->route('rural-health-units.create')
            ->with('success', 'Application submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $firestore = app(FirebaseService::class)->getFirestore();
        $doc = $firestore->collection('rhu')->document($id)->snapshot();
        
        if (!$doc->exists()) {
            abort(404);
        }
        
        $ruralHealthUnit = (object) array_merge(['id' => $id], $doc->data());
        
        return view('admin.show', compact('ruralHealthUnit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RuralHealthUnit $ruralHealthUnit)
    {
        $ruralHealthUnits = RuralHealthUnit::where('status', 'pending')->latest()->get();
        return view('admin.edit', compact('ruralHealthUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RuralHealthUnit $ruralHealthUnit)
    {
        $rhu = RuralHealthUnit::findOrFail($id);
        if ($request->has('status')) {
            $rhu->status = $request->input('status');
            $rhu->save();
            return back()->with('success', 'RHU approved successfully!');
        }
        return back()->with('error', 'No status provided.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RuralHealthUnit $ruralHealthUnit)
    {
        //
    }
}