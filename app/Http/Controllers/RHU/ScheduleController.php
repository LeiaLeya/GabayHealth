<?php

namespace App\Http\Controllers\RHU;

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

    /**
     * Get all barangays that belong to this RHU
     */
    private function getBarangaysUnderRhu(string $rhuId): array
    {
        $barangays = [];
        try {
            $barangayDocs = $this->firestore
                ->collection('barangay')
                ->where('rhuId', '=', $rhuId)
                ->where('status', '=', 'approved')
                ->documents();

            foreach ($barangayDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $barangays[] = [
                        'id' => $doc->id(),
                        'name' => $data['healthCenterName'] ?? $data['name'] ?? 'Barangay',
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching barangays under RHU: ' . $e->getMessage());
        }
        return $barangays;
    }

    public function index(Request $request)
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access schedule management.');
        }
        
        // Get RHU ID
        $rhuId = $this->getBarangayId();
        
        // Get barangays under this RHU
        $barangays = $this->getBarangaysUnderRhu($rhuId);
        
        // Create barangay options with RHU at the top
        $rhuName = $user['name'] ?? 'RHU';
        $rhuOption = ['id' => $rhuId, 'name' => $rhuName . ' (RHU Level)'];
        $barangayOptions = array_merge([$rhuOption], $barangays);
        
        // Get selected barangay from request or use RHU as default
        $selectedBarangayId = $request->get('barangay_id');
        if (!$selectedBarangayId) {
            $selectedBarangayId = $rhuId; // Default to RHU level
        }
        
        $midwifeSchedules = [];
        $doctorSchedules = [];
        $availableMidwives = [];
        $assignedDoctors = [];
        
        try {
            \Log::info('RHU ScheduleController - Fetching schedules for user: ' . $user['id'] . ' with role: ' . $user['role'] . ', barangay: ' . $selectedBarangayId);
            
            // Check if selected is RHU-level or barangay-level
            $isRhuLevel = ($selectedBarangayId === $rhuId);
            
            if ($selectedBarangayId) {
                // Get schedules from the selected location (RHU or barangay)
                if ($isRhuLevel) {
                    $schedulesQuery = $this->firestore
                        ->collection("rhu/{$rhuId}/schedules")
                        ->limit(50)
                        ->documents();
                } else {
                    $schedulesQuery = $this->firestore
                        ->collection("barangay/{$selectedBarangayId}/schedules")
                        ->limit(50)
                        ->documents();
                }

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
            } else {
                // Aggregate schedules from all barangays under this RHU
                foreach ($barangays as $barangay) {
                    $schedulesQuery = $this->firestore
                        ->collection("barangay/{$barangay['id']}/schedules")
                        ->limit(50)
                        ->documents();
                    
                    foreach ($schedulesQuery as $doc) {
                        if ($doc->exists()) {
                            $scheduleData = array_merge($doc->data(), ['id' => $doc->id(), 'barangay_id' => $barangay['id'], 'barangay_name' => $barangay['name']]);
                            
                            if (($scheduleData['type'] ?? '') === 'midwife') {
                                $midwifeSchedules[] = $scheduleData;
                            } elseif (($scheduleData['type'] ?? '') === 'doctor') {
                                $doctorSchedules[] = $scheduleData;
                            } else {
                                $midwifeSchedules[] = $scheduleData;
                            }
                        }
                    }
                }
                $count = count($midwifeSchedules) + count($doctorSchedules);
            }
            
            // Get available midwives from RHU's accounts collection
            try {
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
            } catch (\Exception $e) {
                \Log::warning('Error fetching midwives: ' . $e->getMessage());
            }
            
            // Get assigned doctors from RHU's accounts collection
            try {
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
            } catch (\Exception $e) {
                \Log::warning('Error fetching doctors: ' . $e->getMessage());
            }
            
            \Log::info('RHU ScheduleController - Found ' . $count . ' schedules, ' . count($availableMidwives) . ' midwives, ' . count($assignedDoctors) . ' doctors');

            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors', 'barangayOptions', 'selectedBarangayId'));
        } catch (\Exception $e) {
            \Log::error('Error fetching schedules: ' . $e->getMessage());
            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors', 'barangayOptions', 'selectedBarangayId'))->with('error', 'Error loading schedules data. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            $request->validate([
                'type' => 'required|in:midwife,doctor',
                'personnel_id' => 'required|string',
                'personnel_name' => 'required|string',
                'week_start' => 'required|date',
                'week_end' => 'required|date|after_or_equal:week_start',
                'schedule' => 'nullable|array',
                'schedule.*' => 'nullable|array',
                'schedule.*.*' => 'nullable|string',
                'barangay_id' => 'required|string', // Can be RHU ID or barangay ID
            ]);

            // Get RHU ID
            $rhuId = $this->getBarangayId();
            
            // Check if barangay_id is the RHU ID itself (RHU-level schedule)
            $isRhuLevelSchedule = ($request->barangay_id === $rhuId);
            
            if (!$isRhuLevelSchedule) {
                // Verify that the barangay belongs to this RHU
                $barangays = $this->getBarangaysUnderRhu($rhuId);
                $barangayIds = array_column($barangays, 'id');
                if (!in_array($request->barangay_id, $barangayIds)) {
                    return redirect()->back()->with('error', 'Invalid barangay selected.');
                }
            }

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

            // Store schedule in appropriate location
            if ($isRhuLevelSchedule) {
                // Store RHU-level schedule in rhu/{rhuId}/schedules
                $this->firestore
                    ->collection("rhu/{$rhuId}/schedules")
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
            } else {
                // Store barangay-level schedule in barangay/{barangayId}/schedules
                $this->firestore
                    ->collection("barangay/{$request->barangay_id}/schedules")
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
            }

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
            
            $request->validate([
                'week_start' => 'required|date',
                'week_end' => 'required|date|after_or_equal:week_start',
                'schedule' => 'nullable|array',
                'schedule.*' => 'nullable|array',
                'schedule.*.*' => 'nullable|string',
                'barangay_id' => 'required|string', // Can be RHU ID or barangay ID
            ]);

            // Get RHU ID
            $rhuId = $this->getBarangayId();
            
            // Check if barangay_id is the RHU ID itself (RHU-level schedule)
            $isRhuLevelSchedule = ($request->barangay_id === $rhuId);
            
            if (!$isRhuLevelSchedule) {
                // Verify that the barangay belongs to this RHU
                $barangays = $this->getBarangaysUnderRhu($rhuId);
                $barangayIds = array_column($barangays, 'id');
                if (!in_array($request->barangay_id, $barangayIds)) {
                    return redirect()->back()->with('error', 'Invalid barangay selected.');
                }
            }

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

            // Update in appropriate location
            if ($isRhuLevelSchedule) {
                $this->firestore
                    ->collection("rhu/{$rhuId}/schedules")
                    ->document($id)
                    ->update([
                        ['path' => 'week_start', 'value' => $request->week_start],
                        ['path' => 'week_end', 'value' => $request->week_end],
                        ['path' => 'schedule', 'value' => $scheduleData],
                        ['path' => 'updated_at', 'value' => now()->toISOString()]
                    ]);
            } else {
                $this->firestore
                    ->collection("barangay/{$request->barangay_id}/schedules")
                    ->document($id)
                    ->update([
                        ['path' => 'week_start', 'value' => $request->week_start],
                        ['path' => 'week_end', 'value' => $request->week_end],
                        ['path' => 'schedule', 'value' => $scheduleData],
                        ['path' => 'updated_at', 'value' => now()->toISOString()]
                    ]);
            }

            return redirect()->back()->with('success', 'Weekly schedule updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to access schedule management.');
            }
            
            $barangayId = $request->get('barangay_id');
            if (!$barangayId) {
                return redirect()->back()->with('error', 'Barangay ID is required.');
            }

            // Get RHU ID
            $rhuId = $this->getBarangayId();
            
            // Check if barangayId is the RHU ID itself (RHU-level schedule)
            $isRhuLevelSchedule = ($barangayId === $rhuId);
            
            if (!$isRhuLevelSchedule) {
                // Verify that the barangay belongs to this RHU
                $barangays = $this->getBarangaysUnderRhu($rhuId);
                $barangayIds = array_column($barangays, 'id');
                if (!in_array($barangayId, $barangayIds)) {
                    return redirect()->back()->with('error', 'Invalid barangay selected.');
                }
            }
            
            // Delete from appropriate location
            if ($isRhuLevelSchedule) {
                $this->firestore
                    ->collection("rhu/{$rhuId}/schedules")
                    ->document($id)
                    ->delete();
            } else {
                $this->firestore
                    ->collection("barangay/{$barangayId}/schedules")
                    ->document($id)
                    ->delete();
            }

            return redirect()->back()->with('success', 'Schedule deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    public function getAssignedDoctors(Request $request)
    {
        try {
            $user = session('user');
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $barangayId = $request->get('barangay_id');
            if (!$barangayId) {
                return response()->json(['error' => 'Barangay ID is required'], 400);
            }

            // Verify that the barangay belongs to this RHU
            $barangays = $this->getBarangaysUnderRhu($user['id']);
            $barangayIds = array_column($barangays, 'id');
            if (!in_array($barangayId, $barangayIds)) {
                return response()->json(['error' => 'Invalid barangay selected'], 400);
            }
            
            // Get assigned doctors from the barangay's collection (original structure)
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


