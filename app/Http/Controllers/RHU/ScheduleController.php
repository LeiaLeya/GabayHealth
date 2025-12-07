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
        
        // Get barangays under this RHU
        $barangays = $this->getBarangaysUnderRhu($user['id']);
        
        // Get selected barangay from request or use first one
        $selectedBarangayId = $request->get('barangay_id');
        if (!$selectedBarangayId && !empty($barangays)) {
            $selectedBarangayId = $barangays[0]['id'];
        }
        
        $midwifeSchedules = [];
        $doctorSchedules = [];
        $availableMidwives = [];
        $assignedDoctors = [];
        
        try {
            \Log::info('RHU ScheduleController - Fetching schedules for user: ' . $user['id'] . ' with role: ' . $user['role'] . ', barangay: ' . $selectedBarangayId);
            
            // If a barangay is selected, get schedules from that barangay
            // Otherwise, aggregate schedules from all barangays under this RHU
            if ($selectedBarangayId) {
                // Get schedules from the selected barangay
                $schedulesQuery = $this->firestore
                    ->collection("barangay/{$selectedBarangayId}/schedules")
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

            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors', 'barangays', 'selectedBarangayId'));
        } catch (\Exception $e) {
            \Log::error('Error fetching schedules: ' . $e->getMessage());
            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors', 'barangays', 'selectedBarangayId'))->with('error', 'Error loading schedules data. Please try again.');
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
                'schedule' => 'required|array',
                'schedule.*' => 'required|array',
                'schedule.*.*' => 'nullable|string',
                'barangay_id' => 'required|string', // Required for RHU to specify which barangay
            ]);

            // Verify that the barangay belongs to this RHU
            $barangays = $this->getBarangaysUnderRhu($user['id']);
            $barangayIds = array_column($barangays, 'id');
            if (!in_array($request->barangay_id, $barangayIds)) {
                return redirect()->back()->with('error', 'Invalid barangay selected.');
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

            // Store schedule in the barangay's schedules collection (original structure)
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
                'schedule' => 'required|array',
                'schedule.*' => 'required|array',
                'schedule.*.*' => 'nullable|string',
                'barangay_id' => 'required|string', // Required to know which barangay's schedule to update
            ]);

            // Verify that the barangay belongs to this RHU
            $barangays = $this->getBarangaysUnderRhu($user['id']);
            $barangayIds = array_column($barangays, 'id');
            if (!in_array($request->barangay_id, $barangayIds)) {
                return redirect()->back()->with('error', 'Invalid barangay selected.');
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

            // Update in the barangay's schedules collection (original structure)
            $this->firestore
                ->collection("barangay/{$request->barangay_id}/schedules")
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

            // Verify that the barangay belongs to this RHU
            $barangays = $this->getBarangaysUnderRhu($user['id']);
            $barangayIds = array_column($barangays, 'id');
            if (!in_array($barangayId, $barangayIds)) {
                return redirect()->back()->with('error', 'Invalid barangay selected.');
            }
            
            // Delete from the barangay's schedules collection (original structure)
            $this->firestore
                ->collection("barangay/{$barangayId}/schedules")
                ->document($id)
                ->delete();

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


