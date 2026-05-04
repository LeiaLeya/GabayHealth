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
            // continue with empty list
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

        $rhuId = $this->getBarangayId();
        $barangays = $this->getBarangaysUnderRhu($rhuId);

        $rhuName = $user['name'] ?? 'RHU';
        $rhuOption = ['id' => $rhuId, 'name' => $rhuName . ' (RHU Level)'];
        $barangayOptions = array_merge([$rhuOption], $barangays);

        $selectedBarangayId = $request->get('barangay_id') ?? $rhuId;

        $midwifeSchedules = [];
        $doctorSchedules = [];
        $availableMidwives = [];
        $assignedDoctors = [];

        try {
            $isRhuLevel = ($selectedBarangayId === $rhuId);

            if ($selectedBarangayId) {
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

                foreach ($schedulesQuery as $doc) {
                    if ($doc->exists()) {
                        $scheduleData = array_merge($doc->data(), ['id' => $doc->id()]);

                        if (($scheduleData['type'] ?? '') === 'doctor') {
                            $doctorSchedules[] = $scheduleData;
                        } else {
                            $midwifeSchedules[] = $scheduleData;
                        }
                    }
                }
            } else {
                foreach ($barangays as $barangay) {
                    $schedulesQuery = $this->firestore
                        ->collection("barangay/{$barangay['id']}/schedules")
                        ->limit(50)
                        ->documents();

                    foreach ($schedulesQuery as $doc) {
                        if ($doc->exists()) {
                            $scheduleData = array_merge($doc->data(), ['id' => $doc->id(), 'barangay_id' => $barangay['id'], 'barangay_name' => $barangay['name']]);

                            if (($scheduleData['type'] ?? '') === 'doctor') {
                                $doctorSchedules[] = $scheduleData;
                            } else {
                                $midwifeSchedules[] = $scheduleData;
                            }
                        }
                    }
                }
            }

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
                        $availableMidwives[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    }
                }
            } catch (\Exception $e) {
                // continue with empty midwives
            }

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
                        $assignedDoctors[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    }
                }
            } catch (\Exception $e) {
                // continue with empty doctors
            }

            return $this->view('schedules.index', compact('midwifeSchedules', 'doctorSchedules', 'availableMidwives', 'assignedDoctors', 'barangayOptions', 'selectedBarangayId'));
        } catch (\Exception $e) {
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
                'barangay_id' => 'required|string',
            ]);

            $rhuId = $this->getBarangayId();
            $isRhuLevelSchedule = ($request->barangay_id === $rhuId);

            if (!$isRhuLevelSchedule) {
                $barangays = $this->getBarangaysUnderRhu($rhuId);
                $barangayIds = array_column($barangays, 'id');
                if (!in_array($request->barangay_id, $barangayIds)) {
                    return redirect()->back()->with('error', 'Invalid barangay selected.');
                }
            }

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

            $collectionPath = $isRhuLevelSchedule
                ? "rhu/{$rhuId}/schedules"
                : "barangay/{$request->barangay_id}/schedules";

            $this->firestore
                ->collection($collectionPath)
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
                'schedule' => 'nullable|array',
                'schedule.*' => 'nullable|array',
                'schedule.*.*' => 'nullable|string',
                'barangay_id' => 'required|string',
            ]);

            $rhuId = $this->getBarangayId();
            $isRhuLevelSchedule = ($request->barangay_id === $rhuId);

            if (!$isRhuLevelSchedule) {
                $barangays = $this->getBarangaysUnderRhu($rhuId);
                $barangayIds = array_column($barangays, 'id');
                if (!in_array($request->barangay_id, $barangayIds)) {
                    return redirect()->back()->with('error', 'Invalid barangay selected.');
                }
            }

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

            $collectionPath = $isRhuLevelSchedule
                ? "rhu/{$rhuId}/schedules"
                : "barangay/{$request->barangay_id}/schedules";

            $this->firestore
                ->collection($collectionPath)
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

            $rhuId = $this->getBarangayId();
            $isRhuLevelSchedule = ($barangayId === $rhuId);

            if (!$isRhuLevelSchedule) {
                $barangays = $this->getBarangaysUnderRhu($rhuId);
                $barangayIds = array_column($barangays, 'id');
                if (!in_array($barangayId, $barangayIds)) {
                    return redirect()->back()->with('error', 'Invalid barangay selected.');
                }
            }

            $collectionPath = $isRhuLevelSchedule
                ? "rhu/{$rhuId}/schedules"
                : "barangay/{$barangayId}/schedules";

            $this->firestore
                ->collection($collectionPath)
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

            $barangays = $this->getBarangaysUnderRhu($user['id']);
            $barangayIds = array_column($barangays, 'id');
            if (!in_array($barangayId, $barangayIds)) {
                return response()->json(['error' => 'Invalid barangay selected'], 400);
            }

            $assignedDoctorsQuery = $this->firestore
                ->collection("barangay/{$barangayId}/assignedDoctors")
                ->documents();

            $assignedDoctors = [];
            foreach ($assignedDoctorsQuery as $doc) {
                if ($doc->exists()) {
                    $assignedDoctors[] = array_merge($doc->data(), ['id' => $doc->id()]);
                }
            }

            return response()->json($assignedDoctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
