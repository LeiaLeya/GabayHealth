<?php

namespace App\Http\Controllers; 

use App\Models\RuralHealthUnit;
use Illuminate\Http\Request;


class RuralHealthUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ruralHealthUnits = RuralHealthUnit::where('status', 'approved')->latest()->paginate(8);
        
        return view('ruralHealthUnit.index', compact('ruralHealthUnits'));
    }

    public function indexApprovals()
    {
        $ruralHealthUnits = RuralHealthUnit::where('status', 'pending')->latest()->paginate(5);
        

        return view('ruralHealthUnit.indexApprovals', compact('ruralHealthUnits')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ruralHealthUnits = RuralHealthUnit::latest()->get();
        return view('ruralHealthUnit.create', compact('ruralHealthUnits'));
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
    public function show(RuralHealthUnit $ruralHealthUnit)
    {
        return view('ruralHealthUnit.show', compact('ruralHealthUnit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RuralHealthUnit $ruralHealthUnit)
    {
        $ruralHealthUnits = RuralHealthUnit::where('status', 'pending')->latest()->get();
        return view('RuralHealthUnit.edit', compact('ruralHealthUnits'));
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