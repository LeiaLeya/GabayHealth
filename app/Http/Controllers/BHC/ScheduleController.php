<?php

namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class ScheduleController extends Controller
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
            return redirect()->route('login')->with('error', 'Please login to access schedule management.');
        }
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }
        
        $midwifeSchedules = [];
        $doctorSchedules = [];
        $availableMidwives = [];
        $assignedDoctors = [];
        
        try {
            \Log::info('BHC ScheduleController - Fetching schedules for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            $schedulesQuery = $this->firestore
                ->collection("barangay/{$barangayId}/schedules")
                ->limit(50)
                ->documents();

            $count = 0;
            foreach ($schedulesQuery as $doc) {
                if ($doc->exists()) {
                    $scheduleData = array_merge($doc->data(), ['id' => $doc->id()]);
                    
                    if (($scheduleData['type'] ?? '') === 'midwife') {
                        $midwifeSchedules[] = $scheduleData;
                    } elseif (($scheduleData['type'] ?? '') === 'doctor') {
                        $doctorSchedules[] = $scheduleData;
                    } else {
                        $midwifeSchedules[] = $scheduleData;
                    }
                    $count++;
                }
            }
            
            $midwivesQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('accounts')
                ->where('role', '=', 'midwife')
                ->where('status', '=', 'active')
                ->limit(50)
                ->documents();

            foreach ($midwivesQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $availableMidwives[] = array_merge($data, ['id' => $doc->id()]);
                }
            }
            
            $doctorsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('accounts')
                ->where('role', '=', 'doctor')
                ->where('status', '=', 'active')
                ->limit(50)
                ->documents();

            foreach ($doctorsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $assignedDoctors[] = array_merge($data, ['id' => $doc->id()]);
                }
            }
            
            \Log::info('BHC ScheduleController - Found ' . $count . ' schedules, ' . count($availableMidwives) . ' midwives, ' . count($assignedDoctors) . ' doctors');

            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors'));
        } catch (\Exception $e) {
            \Log::error('Error fetching schedules: ' . $e->getMessage());
            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors'))->with('error', 'Error loading schedules data. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            $barangayId = $this->getBarangayId();
            
            if (!$barangayId) {
                return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
            }
            
            $request->validate([
                'type' => 'required|in:midwife,doctor',
                'personnel_id' => 'required|string',
                'personnel_name' => 'required|string',
                'week_start' => 'required|date',
                'week_end' => 'required|date|after_or_equal:week_start',
                'schedule' => 'required|array',
                'schedule.*' => 'required|array',
                'schedule.*.*' => 'nullable|string'
            ]);

            $scheduleData = [];
            if (!empty($request->schedule) && is_array($request->schedule)) {
                foreach ($request->schedule as $day => $timeSlots) {
                    $formattedSlots = [];
                    if (is_array($timeSlots)) {
                        foreach ($timeSlots as $slot) {
                            if (!empty($slot)) {
                                $formattedSlots[] = $slot;
                            }
                        }
                    }
                    if (!empty($formattedSlots)) {
                        $scheduleData[$day] = $formattedSlots;
                    }
                }
            }

            $this->firestore
                ->collection("barangay/{$barangayId}/schedules")
                ->add([
                    'type' => $request->type,
                    'personnel_id' => $request->personnel_id,
                    'personnel_name' => $request->personnel_name,
                    'week_start' => $request->week_start,
                    'week_end' => $request->week_end,
                    'schedule' => $scheduleData,
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString()
                ]);

            return redirect()->back()->with('success', 'Weekly schedule created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            $barangayId = $this->getBarangayId();
            
            if (!$barangayId) {
                return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
            }
            
            $request->validate([
                'week_start' => 'required|date',
                'week_end' => 'required|date|after_or_equal:week_start',
                'schedule' => 'required|array',
                'schedule.*' => 'required|array',
                'schedule.*.*' => 'nullable|string'
            ]);

            $scheduleData = [];
            if (!empty($request->schedule) && is_array($request->schedule)) {
                foreach ($request->schedule as $day => $timeSlots) {
                    $formattedSlots = [];
                    if (is_array($timeSlots)) {
                        foreach ($timeSlots as $slot) {
                            if (!empty($slot)) {
                                $formattedSlots[] = $slot;
                            }
                        }
                    }
                    if (!empty($formattedSlots)) {
                        $scheduleData[$day] = $formattedSlots;
                    }
                }
            }

            $this->firestore
                ->collection("barangay/{$barangayId}/schedules")
                ->document($id)
                ->update([
                    ['path' => 'week_start', 'value' => $request->week_start],
                    ['path' => 'week_end', 'value' => $request->week_end],
                    ['path' => 'schedule', 'value' => $scheduleData],
                    ['path' => 'updated_at', 'value' => now()->toISOString()]
                ]);

            return redirect()->back()->with('success', 'Weekly schedule updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            $barangayId = $this->getBarangayId();
            
            if (!$barangayId) {
                return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
            }
            
            $this->firestore
                ->collection("barangay/{$barangayId}/schedules")
                ->document($id)
                ->delete();

            return redirect()->back()->with('success', 'Schedule deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    public function getAssignedDoctors()
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $barangayId = $this->getBarangayId();
            
            if (!$barangayId) {
                return response()->json(['error' => 'Barangay ID not found'], 400);
            }
            
            $assignedDoctorsQuery = $this->firestore
                ->collection("barangay/{$barangayId}/assignedDoctors")
                ->documents();

            $assignedDoctors = [];
            foreach ($assignedDoctorsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $assignedDoctors[] = array_merge($data, ['id' => $doc->id()]);
                }
            }

            return response()->json($assignedDoctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

