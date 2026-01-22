<?php

namespace App\Http\Controllers\BHC;

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

    public function index()
    {
        // Set timeout to prevent execution timeout
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access calendar management.');
        }
        
        // Determine barangayId: barangay users use their own id; others use assigned barangayId
        $this->barangayId = $user['role'] === 'barangay'
            ? ($user['id'] ?? null)
            : ($user['barangayId'] ?? null);
        
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }
        
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

            // Get weekly schedules from barangay schedules collection
            $schedulesQuery = $this->firestore
                ->collection("barangay/{$this->barangayId}/schedules")
                ->limit(30) // Limit results to prevent timeout
                ->documents();

            $schedules = [];
            $scheduleCount = 0;
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

            // Fetch appointments for this barangay (subcollection first, then fallback to legacy top-level)
            $appointments = [];
            $appointmentCount = 0;
            $appointmentsQuery = $this->firestore
                ->collection("barangay/{$this->barangayId}/appointments")
                ->limit(100)
                ->documents();

            $processedAny = false;
            foreach ($appointmentsQuery as $doc) {
                if ($doc->exists()) {
                    $processedAny = $this->addAppointmentToGroupedItems($doc, $groupedItems, $appointments, $appointmentCount) || $processedAny;
                }
            }

            // Fallback: check legacy top-level collection if none found
            if (!$processedAny) {
                $legacyQuery = $this->firestore
                    ->collection('appointments')
                    ->where('barangayId', '=', $this->barangayId)
                    ->limit(100)
                    ->documents();

                foreach ($legacyQuery as $doc) {
                    if ($doc->exists()) {
                        $processedAny = $this->addAppointmentToGroupedItems($doc, $groupedItems, $appointments, $appointmentCount) || $processedAny;
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
            return $this->view('calendars.index', compact('calendarEvents', 'currentMonth', 'groupedItems', 'availableMidwives', 'assignedDoctors'));
        } catch (\Exception $e) {
            \Log::error('Error fetching calendar data: ' . $e->getMessage());
            return $this->view('calendars.index', compact('calendarEvents', 'currentMonth', 'groupedItems', 'availableMidwives', 'assignedDoctors'))->with('error', 'Error loading calendar data. Please try again.');
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
        
        // Get barangayId from user session
        $this->barangayId = $user['role'] === 'barangay'
            ? ($user['id'] ?? null)
            : ($user['barangayId'] ?? null);
        
        if (!$this->barangayId) {
            return response()->json(['error' => 'Barangay ID not found'], 400);
        }
        
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

            // Get weekly schedules from barangay schedules collection (same logic as index method)
            $schedulesQuery = $this->firestore
                ->collection("barangay/{$this->barangayId}/schedules")
                ->limit(30)
                ->documents();

            $schedules = [];
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

            // Fetch appointments for this barangay (subcollection first, then fallback to legacy top-level)
            $appointmentsQuery = $this->firestore
                ->collection("barangay/{$this->barangayId}/appointments")
                ->limit(100)
                ->documents();

            $processedAny = false;
            foreach ($appointmentsQuery as $doc) {
                if ($doc->exists()) {
                    $processedAny = $this->addAppointmentToGroupedItems($doc, $groupedItems) || $processedAny;
                }
            }

            // Fallback: check legacy top-level collection if none found
            if (!$processedAny) {
                $legacyQuery = $this->firestore
                    ->collection('appointments')
                    ->where('barangayId', '=', $this->barangayId)
                    ->limit(100)
                    ->documents();

                foreach ($legacyQuery as $doc) {
                    if ($doc->exists()) {
                        $processedAny = $this->addAppointmentToGroupedItems($doc, $groupedItems) || $processedAny;
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