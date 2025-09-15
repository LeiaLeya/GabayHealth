<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class ScheduleController extends Controller
{
    protected $firestore;
    protected $barangayId;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
    }

    public function index()
    {
        // Set timeout to prevent execution timeout
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access schedule management.');
        }
        
        // Get barangayId from user session
        $this->barangayId = $user['barangayId'] ?? null;
        
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }
        
        // Initialize variables as empty arrays (view expects $midwifeSchedules and $doctorSchedules)
        $midwifeSchedules = [];
        $doctorSchedules = [];
        $availableMidwives = [];
        $assignedDoctors = [];
        
        try {
            \Log::info('ScheduleController - Fetching schedules for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            // Get schedules from barangay schedules collection
            $schedulesQuery = $this->firestore
                ->collection("barangay/{$this->barangayId}/schedules")
                ->limit(50) // Limit results to prevent timeout
                ->documents();

            $count = 0;
            foreach ($schedulesQuery as $doc) {
                if ($doc->exists()) {
                    $scheduleData = array_merge($doc->data(), ['id' => $doc->id()]);
                    
                    // Categorize schedules by type
                    if (($scheduleData['type'] ?? '') === 'midwife') {
                        $midwifeSchedules[] = $scheduleData;
                    } elseif (($scheduleData['type'] ?? '') === 'doctor') {
                        $doctorSchedules[] = $scheduleData;
                    } else {
                        // Default to midwife if no type specified
                        $midwifeSchedules[] = $scheduleData;
                    }
                    $count++;
                }
            }
            
            // Fetch available midwives from accounts subcollection
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
            
            // Fetch assigned doctors from accounts subcollection
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
            
            \Log::info('ScheduleController - Found ' . $count . ' schedules, ' . count($availableMidwives) . ' midwives, ' . count($assignedDoctors) . ' doctors');

            return view('pages.schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors'));
        } catch (\Exception $e) {
            \Log::error('Error fetching schedules: ' . $e->getMessage());
            return view('pages.schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors'))->with('error', 'Error loading schedules data. Please try again.');
        }
    }

    // Store a new schedule
    public function store(Request $request)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            // Get barangayId from user session
            $this->barangayId = $user['barangayId'] ?? null;
            
            if (!$this->barangayId) {
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
            foreach ($request->schedule as $day => $timeSlots) {
                $formattedSlots = [];
                foreach ($timeSlots as $slot) {
                    if (!empty($slot)) {
                        $formattedSlots[] = $slot;
                    }
                }
                if (!empty($formattedSlots)) {
                    $scheduleData[$day] = $formattedSlots;
                }
            }

            $this->firestore
                ->collection("barangay/{$this->barangayId}/schedules")
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

    // Update a schedule
    public function update(Request $request, $id)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            // Get barangayId from user session
            $this->barangayId = $user['barangayId'] ?? null;
            
            if (!$this->barangayId) {
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
            foreach ($request->schedule as $day => $timeSlots) {
                $formattedSlots = [];
                foreach ($timeSlots as $slot) {
                    if (!empty($slot)) {
                        $formattedSlots[] = $slot;
                    }
                }
                if (!empty($formattedSlots)) {
                    $scheduleData[$day] = $formattedSlots;
                }
            }

            $this->firestore
                ->collection("barangay/{$this->barangayId}/schedules")
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

    // Delete a schedule
    public function destroy($id)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            // Get barangayId from user session
            $this->barangayId = $user['barangayId'] ?? null;
            
            if (!$this->barangayId) {
                return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
            }
            
            $this->firestore
                ->collection("barangay/{$this->barangayId}/schedules")
                ->document($id)
                ->delete();

            return redirect()->back()->with('success', 'Schedule deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    // Get assigned doctors from RHU
    public function getAssignedDoctors()
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            // Get barangayId from user session
            $this->barangayId = $user['barangayId'] ?? null;
            
            if (!$this->barangayId) {
                return response()->json(['error' => 'Barangay ID not found'], 400);
            }
            
            $assignedDoctorsQuery = $this->firestore
                ->collection("barangay/{$this->barangayId}/assignedDoctors")
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