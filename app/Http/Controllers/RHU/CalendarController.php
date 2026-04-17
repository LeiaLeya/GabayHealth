<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Carbon\Carbon;

class CalendarController extends Controller
{
    use HasRoleContext;

    protected $firestore;
    protected $barangayId;

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

    public function index()
    {
        // Set timeout to prevent execution timeout
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access calendar management.');
        }
        
        // Get RHU ID
        $rhuId = $this->getBarangayId();
        // Determine barangayId: barangay users use their own id; others use assigned barangayId
        $this->barangayId = $user['role'] === 'barangay'
            ? ($user['id'] ?? null)
            : ($user['barangayId'] ?? null);
        
        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }
        
        // Get barangays under this RHU
        $barangays = $this->getBarangaysUnderRhu($rhuId);
        
        // Get RHU data to include in barangay options
        $rhuName = $user['name'] ?? 'RHU';
        $rhuOption = ['id' => $rhuId, 'name' => $rhuName . ' (RHU Level)'];
        
        // Prepend RHU option to barangays list
        $barangayOptions = array_merge([$rhuOption], $barangays);
        
        // Initialize variables as empty arrays (view expects $calendarEvents, $currentMonth, and $groupedItems)
        $calendarEvents = [];
        $currentMonth = now()->format('Y-m');
        $groupedItems = [];
        $availableMidwives = [];
        $assignedDoctors = [];
        
        try {
            \Log::info('CalendarController - Fetching calendar data for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            // Get events; for barangay users, always use the resolved barangayId
            $eventCollection = $user['role'];
            $eventDocId = $user['role'] === 'barangay' ? $this->barangayId : $user['id'];

            $eventsQuery = $this->firestore
                ->collection($eventCollection)
                ->document($eventDocId)
                ->collection('events')
                ->limit(30) // Limit results to prevent timeout
                ->documents();

            $events = [];
            $eventCount = 0;
            foreach ($eventsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    \Log::info('Found event data:', $data);
                    $eventData = [
                        'id' => $doc->id(),
                        'title' => $data['title'] ?? 'Untitled Event',
                        'start' => $data['date'] . 'T' . ($data['start_time'] ?? '09:00'),
                        'end' => $data['date'] . 'T' . ($data['end_time'] ?? '17:00'),
                        'description' => $data['description'] ?? '',
                        'type' => $data['type'] ?? 'event',
                        'date' => $data['date'] ?? '',
                        'start_time' => $data['start_time'] ?? '',
                        'end_time' => $data['end_time'] ?? '',
                        'time' => $data['time'] ?? '',
                        'location' => $data['location'] ?? '',
                        'in_charge' => $data['in_charge'] ?? '',
                        'status' => $data['status'] ?? 'Upcoming',
                        'isOpenToAll' => $data['isOpenToAll'] ?? false,
                        'targetAttendees' => $data['targetAttendees'] ?? ''
                    ];
                    $events[] = $eventData;
                    
                    // Group by date for JavaScript
                    $date = $data['date'] ?? Carbon::now()->format('Y-m-d');
                    if (!isset($groupedItems[$date])) {
                        $groupedItems[$date] = [];
                    }
                    $groupedItems[$date][] = $eventData;
                    
                    $eventCount++;
                }
            }

            // Get weekly schedules from RHU level and all barangays under this RHU
            $schedules = [];
            $scheduleCount = 0;
            
            // First, get RHU-level schedules
            $rhuSchedulesQuery = $this->firestore
                ->collection("rhu/{$rhuId}/schedules")
                ->limit(30)
                ->documents();
            
            foreach ($rhuSchedulesQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    \Log::info('Found RHU-level schedule data:', $data);
                    
                    // Check if schedule is for current week
                    $weekStart = Carbon::parse($data['week_start'] ?? '');
                    $weekEnd = Carbon::parse($data['week_end'] ?? '');
                    $currentDate = Carbon::now();
                    
                    if ($currentDate->between($weekStart, $weekEnd)) {
                        // Process weekly schedule
                        $schedule = $data['schedule'] ?? [];
                        $personnelName = $data['personnel_name'] ?? 'Unknown';
                        $scheduleType = $data['type'] ?? 'midwife';
                        
                        foreach ($schedule as $day => $timeSlots) {
                            // Convert day name to date for current week
                            $dayDate = $this->getDayDateForCurrentWeek($day);
                            
                            if ($dayDate) {
                                $timeSlotIndex = 0;
                                foreach ($timeSlots as $timeSlot) {
                                    if (!empty($timeSlot)) {
                                        $scheduleData = [
                                            'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                            'title' => $personnelName . ' (' . ucfirst($scheduleType) . ') [RHU]',
                                            'start' => $dayDate . 'T' . $this->extractStartTime($timeSlot),
                                            'end' => $dayDate . 'T' . $this->extractEndTime($timeSlot),
                                            'description' => $timeSlot,
                                            'type' => 'schedule',
                                            'personnel_name' => $personnelName,
                                            'schedule_type' => $scheduleType
                                        ];
                                        $schedules[] = $scheduleData;
                                        
                                        // Group by date for JavaScript
                                        if (!isset($groupedItems[$dayDate])) {
                                            $groupedItems[$dayDate] = [];
                                        }
                                        $groupedItems[$dayDate][] = $scheduleData;
                                        
                                        $scheduleCount++;
                                        $timeSlotIndex++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Then, get barangay-level schedules
            foreach ($barangays as $barangay) {
                $schedulesQuery = $this->firestore
                    ->collection("barangay/{$barangay['id']}/schedules")
                    ->limit(30) // Limit results to prevent timeout
                    ->documents();

                foreach ($schedulesQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        \Log::info('Found weekly schedule data:', $data);
                        
                        // Check if schedule is for current week
                        $weekStart = Carbon::parse($data['week_start'] ?? '');
                        $weekEnd = Carbon::parse($data['week_end'] ?? '');
                        $currentDate = Carbon::now();
                        
                        if ($currentDate->between($weekStart, $weekEnd)) {
                            // Process weekly schedule
                            $schedule = $data['schedule'] ?? [];
                            $personnelName = $data['personnel_name'] ?? 'Unknown';
                            $scheduleType = $data['type'] ?? 'midwife';
                            
                            foreach ($schedule as $day => $timeSlots) {
                                // Convert day name to date for current week
                                $dayDate = $this->getDayDateForCurrentWeek($day);
                                
                                if ($dayDate) {
                                    $timeSlotIndex = 0;
                                    foreach ($timeSlots as $timeSlot) {
                                        if (!empty($timeSlot)) {
                                            $scheduleData = [
                                                'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                                'title' => $personnelName . ' (' . ucfirst($scheduleType) . ')',
                                                'start' => $dayDate . 'T' . $this->extractStartTime($timeSlot),
                                                'end' => $dayDate . 'T' . $this->extractEndTime($timeSlot),
                                                'description' => $timeSlot,
                                                'type' => 'schedule',
                                                'personnel_name' => $personnelName,
                                                'schedule_type' => $scheduleType
                                            ];
                                            $schedules[] = $scheduleData;
                                            
                                            // Group by date for JavaScript
                                            if (!isset($groupedItems[$dayDate])) {
                                                $groupedItems[$dayDate] = [];
                                            }
                                            $groupedItems[$dayDate][] = $scheduleData;
                                            
                                            $scheduleCount++;
                                            $timeSlotIndex++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Fetch appointments for RHU's barangays
            $appointments = [];
            $appointmentCount = 0;
            foreach ($barangays as $barangay) {
                $appointmentsQuery = $this->firestore
                    ->collection('appointments')
                    ->where('barangayId', '=', $barangay['id'])
                    ->limit(100)
                    ->documents();

                foreach ($appointmentsQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        \Log::info('Found appointment data:', $data);

                        $parsed = $this->parseAppointmentDate($data['appointmentDate'] ?? '');
                        $appointmentDate = $parsed['date'] ?? null;
                        $startTime24 = $parsed['start_time'] ?? null;
                        $endTime24 = $parsed['end_time'] ?? null;

                        if ($appointmentDate) {
                            $title = ($data['patient']['name'] ?? 'Patient') . ' - ' . ($data['serviceName'] ?? 'Appointment');
                            $appointmentData = [
                                'id' => $doc->id(),
                                'title' => $title,
                                'start' => $appointmentDate . 'T' . ($startTime24 ?? '09:00'),
                                'end' => $appointmentDate . 'T' . ($endTime24 ?? '10:00'),
                                'description' => $data['serviceName'] ?? 'Appointment',
                                'type' => 'appointment',
                                'date' => $appointmentDate,
                                'start_time' => $startTime24,
                                'end_time' => $endTime24,
                                'time' => $data['appointmentDate'] ?? '',
                                'notes' => $data['notes'] ?? '',
                                'patient' => [
                                    'name' => $data['patient']['name'] ?? '',
                                    'gender' => $data['patient']['gender'] ?? '',
                                    'age' => isset($data['patient']['birthdate']) ? $this->calculateAge($data['patient']['birthdate']) : null,
                                ],
                            ];

                            $appointments[] = $appointmentData;

                            if (!isset($groupedItems[$appointmentDate])) {
                                $groupedItems[$appointmentDate] = [];
                            }
                            $groupedItems[$appointmentDate][] = $appointmentData;
                            $appointmentCount++;
                        }
                    }
                }
            }

            // Combine events, schedules, and appointments
            $calendarEvents = array_merge($events, $schedules, $appointments);
            
            \Log::info('CalendarController - Found ' . $eventCount . ' events, ' . $scheduleCount . ' schedules and ' . $appointmentCount . ' appointments');
            \Log::info('CalendarController - Grouped items: ' . json_encode($groupedItems));

            // If no data exists, create some sample data for testing
            if (empty($groupedItems)) {
                \Log::info('CalendarController - No data found, creating sample data for testing');
                $today = Carbon::now()->format('Y-m-d');
                $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
                
                $groupedItems[$today] = [
                    [
                        'id' => 'sample-event-1',
                        'title' => 'Sample Event',
                        'start' => $today . 'T09:00',
                        'end' => $today . 'T10:00',
                        'description' => 'This is a sample event for testing',
                        'type' => 'event'
                    ]
                ];
                
                $groupedItems[$tomorrow] = [
                    [
                        'id' => 'sample-schedule-1',
                        'title' => 'Sample Schedule',
                        'start' => $tomorrow . 'T14:00',
                        'end' => $tomorrow . 'T15:00',
                        'description' => 'This is a sample schedule for testing',
                        'type' => 'schedule'
                    ]
                ];
                
                $calendarEvents = array_merge($groupedItems[$today], $groupedItems[$tomorrow]);
            }

            // Fetch available midwives from accounts subcollection
            \Log::info('CalendarController - Fetching midwives from: ' . $user['role'] . '/' . $user['id'] . '/accounts');
            $midwivesQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('accounts')
                ->where('role', '=', 'midwife')
                ->where('status', '=', 'active')
                ->limit(50)
                ->documents();

            $midwifeCount = 0;
            foreach ($midwivesQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    \Log::info('CalendarController - Found midwife:', $data);
                    $availableMidwives[] = array_merge($data, ['id' => $doc->id()]);
                    $midwifeCount++;
                }
            }
            \Log::info('CalendarController - Total midwives found: ' . $midwifeCount);
            
            // Fetch assigned doctors from accounts subcollection
            \Log::info('CalendarController - Fetching doctors from: ' . $user['role'] . '/' . $user['id'] . '/accounts');
            $doctorsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('accounts')
                ->where('role', '=', 'doctor')
                ->where('status', '=', 'active')
                ->limit(50)
                ->documents();

            $doctorCount = 0;
            foreach ($doctorsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    \Log::info('CalendarController - Found doctor:', $data);
                    $assignedDoctors[] = array_merge($data, ['id' => $doc->id()]);
                    $doctorCount++;
                }
            }
            \Log::info('CalendarController - Total doctors found: ' . $doctorCount);

            \Log::info('CalendarController - Passing to view - availableMidwives count: ' . count($availableMidwives));
            \Log::info('CalendarController - Passing to view - assignedDoctors count: ' . count($assignedDoctors));
            return $this->view('calendars.index', compact('calendarEvents', 'currentMonth', 'groupedItems', 'availableMidwives', 'assignedDoctors', 'barangayOptions'));
        } catch (\Exception $e) {
            \Log::error('Error fetching calendar data: ' . $e->getMessage());
            return $this->view('calendars.index', compact('calendarEvents', 'currentMonth', 'groupedItems', 'availableMidwives', 'assignedDoctors', 'barangayOptions'))->with('error', 'Error loading calendar data. Please try again.');
        }
    }

    private function groupItemsByDate($items)
    {
        $grouped = [];
        
        foreach ($items as $item) {
            $date = $item['date'] ?? Carbon::now()->format('Y-m-d');
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $item;
        }
        
        return $grouped;
    }

    private function getDayDateForCurrentWeek($dayName)
    {
        $today = Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        
        $dayMap = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6
        ];
        
        $dayIndex = $dayMap[strtolower($dayName)] ?? null;
        
        if ($dayIndex !== null) {
            return $startOfWeek->copy()->addDays($dayIndex)->format('Y-m-d');
        }
        
        return null;
    }

    private function extractStartTime($timeSlot)
    {
        // Handle time slots like "08:00 - 17:00"
        if (strpos($timeSlot, ' - ') !== false) {
            $parts = explode(' - ', $timeSlot);
            return trim($parts[0] ?? '08:00');
        }
        return '08:00'; // Default start time
    }

    private function extractEndTime($timeSlot)
    {
        // Handle time slots like "08:00 - 17:00"
        if (strpos($timeSlot, ' - ') !== false) {
            $parts = explode(' - ', $timeSlot);
            return trim($parts[1] ?? '17:00');
        }
        return '17:00'; // Default end time
    }

    private function parseAppointmentDate($appointmentString)
    {
        // Supported formats:
        // 1) "09/22/2025" or "09/22/2025 10:00AM-12:00PM" (MM/DD/YYYY)
        // 2) "monday 10:00AM-12:00PM" or "Monday 10:00 AM - 12:00 PM"
        if (!$appointmentString) {
            return [];
        }

        // Try MM/DD/YYYY with optional time range
        $usDatePattern = '/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{1,2}:\d{2}\s*[APMapm]{2})\s*[-–]\s*(\d{1,2}:\d{2}\s*[APMapm]{2}))?/';
        if (preg_match($usDatePattern, $appointmentString, $m)) {
            $month = (int)$m[1];
            $day = (int)$m[2];
            $year = (int)$m[3];
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $start12 = isset($m[4]) ? strtoupper(str_replace(' ', '', $m[4])) : null;
            $end12 = isset($m[5]) ? strtoupper(str_replace(' ', '', $m[5])) : null;
            return [
                'date' => $date,
                'start_time' => $start12 ? $this->convert12To24($start12) : null,
                'end_time' => $end12 ? $this->convert12To24($end12) : null,
            ];
        }

        $pattern = '/^\s*([A-Za-z]+)\s+(\d{1,2}:\d{2}\s*[APMapm]{2})\s*[-–]\s*(\d{1,2}:\d{2}\s*[APMapm]{2})/';
        if (preg_match($pattern, $appointmentString, $matches)) {
            $dayName = strtolower($matches[1]);
            $start12 = strtoupper(str_replace(' ', '', $matches[2]));
            $end12 = strtoupper(str_replace(' ', '', $matches[3]));

            $date = $this->getDayDateForCurrentWeek($dayName);
            return [
                'date' => $date,
                'start_time' => $this->convert12To24($start12),
                'end_time' => $this->convert12To24($end12),
            ];
        }

        // Fallback: if only day name provided
        $dayOnlyPattern = '/^\s*([A-Za-z]+)/';
        if (preg_match($dayOnlyPattern, $appointmentString, $matches)) {
            $dayName = strtolower($matches[1]);
            $date = $this->getDayDateForCurrentWeek($dayName);
            return [
                'date' => $date,
            ];
        }

        return [];
    }

    private function convert12To24($time12)
    {
        try {
            $time12 = trim($time12);
            $dt = \DateTime::createFromFormat('g:iA', strtoupper($time12));
            if ($dt) {
                return $dt->format('H:i');
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    private function calculateAge($birthdate)
    {
        try {
            $birth = Carbon::parse($birthdate);
            return $birth->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCalendarData(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        
        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        // Get RHU ID
        $rhuId = $this->getBarangayId();
        // Get barangayId from user session
        $this->barangayId = $user['role'] === 'barangay'
            ? ($user['id'] ?? null)
            : ($user['barangayId'] ?? null);
        
        if (!$rhuId) {
            return response()->json(['error' => 'RHU ID not found'], 400);
        }
        
        // Get barangays under this RHU
        $barangays = $this->getBarangaysUnderRhu($rhuId);
        
        // Initialize groupedItems
        $groupedItems = [];
        
        try {
            \Log::info('Calendar - AJAX request for month: ' . $month);
            
            // Get events; for barangay users, always use the resolved barangayId (same logic as index method)
            $eventCollection = $user['role'];
            $eventDocId = $user['role'] === 'barangay' ? $this->barangayId : $user['id'];

            $eventsQuery = $this->firestore
                ->collection($eventCollection)
                ->document($eventDocId)
                ->collection('events')
                ->limit(30)
                ->documents();

            $events = [];
            foreach ($eventsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $eventData = [
                        'id' => $doc->id(),
                        'title' => $data['title'] ?? 'Untitled Event',
                        'start' => $data['date'] . 'T' . ($data['start_time'] ?? '09:00'),
                        'end' => $data['date'] . 'T' . ($data['end_time'] ?? '17:00'),
                        'description' => $data['description'] ?? '',
                        'type' => $data['type'] ?? 'event'
                    ];
                    $events[] = $eventData;
                    
                    // Group by date for JavaScript
                    $date = $data['date'] ?? Carbon::now()->format('Y-m-d');
                    if (!isset($groupedItems[$date])) {
                        $groupedItems[$date] = [];
                    }
                    $groupedItems[$date][] = $eventData;
                }
            }

            // Get weekly schedules from all barangays under this RHU
            foreach ($barangays as $barangay) {
                $schedulesQuery = $this->firestore
                    ->collection("barangay/{$barangay['id']}/schedules")
                    ->limit(30)
                    ->documents();

                foreach ($schedulesQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        
                        // Check if schedule is for current week
                        $weekStart = Carbon::parse($data['week_start'] ?? '');
                        $weekEnd = Carbon::parse($data['week_end'] ?? '');
                        $currentDate = Carbon::now();
                        
                        if ($currentDate->between($weekStart, $weekEnd)) {
                            // Process weekly schedule
                            $schedule = $data['schedule'] ?? [];
                            $personnelName = $data['personnel_name'] ?? 'Unknown';
                            $scheduleType = $data['type'] ?? 'midwife';
                            
                            foreach ($schedule as $day => $timeSlots) {
                                // Convert day name to date for current week
                                $dayDate = $this->getDayDateForCurrentWeek($day);
                                
                                if ($dayDate) {
                                    $timeSlotIndex = 0;
                                    foreach ($timeSlots as $timeSlot) {
                                        if (!empty($timeSlot)) {
                                            $scheduleData = [
                                                'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                                'title' => $personnelName . ' (' . ucfirst($scheduleType) . ')',
                                                'start' => $dayDate . 'T' . $this->extractStartTime($timeSlot),
                                                'end' => $dayDate . 'T' . $this->extractEndTime($timeSlot),
                                                'description' => $timeSlot,
                                                'type' => 'schedule',
                                                'personnel_name' => $personnelName,
                                                'schedule_type' => $scheduleType
                                            ];
                                            
                                            // Group by date for JavaScript
                                            if (!isset($groupedItems[$dayDate])) {
                                                $groupedItems[$dayDate] = [];
                                            }
                                            $groupedItems[$dayDate][] = $scheduleData;
                                            $timeSlotIndex++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Fetch appointments for RHU's barangays
            $appointments = [];
            $appointmentCount = 0;
            foreach ($barangays as $barangay) {
                $appointmentsQuery = $this->firestore
                    ->collection('appointments')
                    ->where('barangayId', '=', $barangay['id'])
                    ->limit(100)
                    ->documents();

                foreach ($appointmentsQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $parsed = $this->parseAppointmentDate($data['appointmentDate'] ?? '');
                        $appointmentDate = $parsed['date'] ?? null;
                        $startTime24 = $parsed['start_time'] ?? null;
                        $endTime24 = $parsed['end_time'] ?? null;

                        if ($appointmentDate) {
                            $title = ($data['patient']['name'] ?? 'Patient') . ' - ' . ($data['serviceName'] ?? 'Appointment');
                            $appointmentData = [
                                'id' => $doc->id(),
                                'title' => $title,
                                'start' => $appointmentDate . 'T' . ($startTime24 ?? '09:00'),
                                'end' => $appointmentDate . 'T' . ($endTime24 ?? '10:00'),
                                'description' => $data['serviceName'] ?? 'Appointment',
                                'type' => 'appointment',
                                'date' => $appointmentDate,
                                'start_time' => $startTime24,
                                'end_time' => $endTime24,
                                'time' => $data['appointmentDate'] ?? '',
                                'notes' => $data['notes'] ?? '',
                                'patient' => [
                                    'name' => $data['patient']['name'] ?? '',
                                    'gender' => $data['patient']['gender'] ?? '',
                                    'age' => isset($data['patient']['birthdate']) ? $this->calculateAge($data['patient']['birthdate']) : null,
                                ],
                            ];

                            $appointments[] = $appointmentData;

                            if (!isset($groupedItems[$appointmentDate])) {
                                $groupedItems[$appointmentDate] = [];
                            }
                            $groupedItems[$appointmentDate][] = $appointmentData;
                            $appointmentCount++;
                        }
                    }
                }
            }

            return response()->json([
                'groupedItems' => $groupedItems,
                'month' => $month
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Calendar - Error fetching calendar data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch calendar data'], 500);
        }
    }
} 

