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

    public function index()
    {
        set_time_limit(60);

        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access calendar management.');
        }

        $rhuId = $this->getBarangayId();
        $this->barangayId = $user['role'] === 'barangay'
            ? ($user['id'] ?? null)
            : ($user['barangayId'] ?? null);

        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $barangays = $this->getBarangaysUnderRhu($rhuId);
        $rhuName = $user['name'] ?? 'RHU';
        $rhuOption = ['id' => $rhuId, 'name' => $rhuName . ' (RHU Level)'];
        $barangayOptions = array_merge([$rhuOption], $barangays);

        $calendarEvents = [];
        $currentMonth = now()->format('Y-m');
        $groupedItems = [];
        $availableMidwives = [];
        $assignedDoctors = [];

        try {
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

                    $date = $data['date'] ?? Carbon::now()->format('Y-m-d');
                    if (!isset($groupedItems[$date])) {
                        $groupedItems[$date] = [];
                    }
                    $groupedItems[$date][] = $eventData;
                }
            }

            $schedules = [];
            $monthStart = Carbon::parse($currentMonth . '-01');
            $monthEnd = $monthStart->copy()->endOfMonth();

            $rhuSchedulesQuery = $this->firestore
                ->collection("rhu/{$rhuId}/schedules")
                ->limit(100)
                ->documents();

            foreach ($rhuSchedulesQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    if (empty($data['week_start'])) continue;

                    $wStart = Carbon::parse($data['week_start']);
                    $wEnd = Carbon::parse($data['week_end'] ?? $data['week_start']);

                    if ($wEnd->lt($monthStart) || $wStart->gt($monthEnd)) continue;

                    $schedule = $data['schedule'] ?? [];
                    $personnelName = $data['personnel_name'] ?? 'Unknown';
                    $scheduleType = $data['type'] ?? 'midwife';

                    foreach ($schedule as $day => $timeSlots) {
                        $dayDate = $this->getDayDateForWeek($day, $data['week_start']);
                        if (!$dayDate) continue;

                        $timeSlotIndex = 0;
                        foreach ($timeSlots as $timeSlot) {
                            if (!empty($timeSlot)) {
                                $scheduleData = [
                                    'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                    'title' => $personnelName . ' (' . ucfirst($scheduleType) . ')',
                                    'description' => $timeSlot,
                                    'type' => 'schedule',
                                    'personnel_name' => $personnelName,
                                    'schedule_type' => $scheduleType,
                                ];
                                $schedules[] = $scheduleData;

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

            foreach ($barangays as $barangay) {
                $schedulesQuery = $this->firestore
                    ->collection("barangay/{$barangay['id']}/schedules")
                    ->limit(100)
                    ->documents();

                foreach ($schedulesQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        if (empty($data['week_start'])) continue;

                        $wStart = Carbon::parse($data['week_start']);
                        $wEnd = Carbon::parse($data['week_end'] ?? $data['week_start']);

                        if ($wEnd->lt($monthStart) || $wStart->gt($monthEnd)) continue;

                        $schedule = $data['schedule'] ?? [];
                        $personnelName = $data['personnel_name'] ?? 'Unknown';
                        $scheduleType = $data['type'] ?? 'midwife';

                        foreach ($schedule as $day => $timeSlots) {
                            $dayDate = $this->getDayDateForWeek($day, $data['week_start']);
                            if (!$dayDate) continue;

                            $timeSlotIndex = 0;
                            foreach ($timeSlots as $timeSlot) {
                                if (!empty($timeSlot)) {
                                    $scheduleData = [
                                        'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                        'title' => $personnelName . ' (' . ucfirst($scheduleType) . ')',
                                        'description' => $timeSlot,
                                        'type' => 'schedule',
                                        'personnel_name' => $personnelName,
                                        'schedule_type' => $scheduleType,
                                    ];
                                    $schedules[] = $scheduleData;

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

            $appointments = [];
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
                        }
                    }
                }
            }

            $calendarEvents = array_merge($events, $schedules, $appointments);

            if (empty($groupedItems)) {
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

            return $this->view('calendars.index', compact('calendarEvents', 'currentMonth', 'groupedItems', 'availableMidwives', 'assignedDoctors', 'barangayOptions'));
        } catch (\Exception $e) {
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

    private function getDayDateForWeek($dayName, $weekStart)
    {
        $dayMap = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $dayIndex = $dayMap[strtolower($dayName)] ?? null;

        if ($dayIndex !== null) {
            return Carbon::parse($weekStart)->addDays($dayIndex)->format('Y-m-d');
        }

        return null;
    }

    private function extractStartTime($timeSlot)
    {
        if (strpos($timeSlot, ' - ') !== false) {
            $parts = explode(' - ', $timeSlot);
            return trim($parts[0] ?? '08:00');
        }
        return '08:00';
    }

    private function extractEndTime($timeSlot)
    {
        if (strpos($timeSlot, ' - ') !== false) {
            $parts = explode(' - ', $timeSlot);
            return trim($parts[1] ?? '17:00');
        }
        return '17:00';
    }

    private function parseAppointmentDate($appointmentString)
    {
        if (!$appointmentString) {
            return [];
        }

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

        $rhuId = $this->getBarangayId();
        $this->barangayId = $user['role'] === 'barangay'
            ? ($user['id'] ?? null)
            : ($user['barangayId'] ?? null);

        if (!$rhuId) {
            return response()->json(['error' => 'RHU ID not found'], 400);
        }

        $barangays = $this->getBarangaysUnderRhu($rhuId);
        $groupedItems = [];

        try {
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

                    $date = $data['date'] ?? Carbon::now()->format('Y-m-d');
                    if (!isset($groupedItems[$date])) {
                        $groupedItems[$date] = [];
                    }
                    $groupedItems[$date][] = $eventData;
                }
            }

            $ajaxMonthStart = Carbon::parse($month . '-01');
            $ajaxMonthEnd = $ajaxMonthStart->copy()->endOfMonth();

            $rhuSchedulesAjax = $this->firestore
                ->collection("rhu/{$rhuId}/schedules")
                ->limit(100)
                ->documents();

            foreach ($rhuSchedulesAjax as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    if (empty($data['week_start'])) continue;

                    $wStart = Carbon::parse($data['week_start']);
                    $wEnd = Carbon::parse($data['week_end'] ?? $data['week_start']);

                    if ($wEnd->lt($ajaxMonthStart) || $wStart->gt($ajaxMonthEnd)) continue;

                    $schedule = $data['schedule'] ?? [];
                    $personnelName = $data['personnel_name'] ?? 'Unknown';
                    $scheduleType = $data['type'] ?? 'midwife';

                    foreach ($schedule as $day => $timeSlots) {
                        $dayDate = $this->getDayDateForWeek($day, $data['week_start']);
                        if (!$dayDate) continue;

                        $timeSlotIndex = 0;
                        foreach ($timeSlots as $timeSlot) {
                            if (!empty($timeSlot)) {
                                $scheduleData = [
                                    'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                    'title' => $personnelName . ' (' . ucfirst($scheduleType) . ')',
                                    'description' => $timeSlot,
                                    'type' => 'schedule',
                                    'personnel_name' => $personnelName,
                                    'schedule_type' => $scheduleType,
                                ];

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

            foreach ($barangays as $barangay) {
                $schedulesQuery = $this->firestore
                    ->collection("barangay/{$barangay['id']}/schedules")
                    ->limit(100)
                    ->documents();

                foreach ($schedulesQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        if (empty($data['week_start'])) continue;

                        $wStart = Carbon::parse($data['week_start']);
                        $wEnd = Carbon::parse($data['week_end'] ?? $data['week_start']);

                        if ($wEnd->lt($ajaxMonthStart) || $wStart->gt($ajaxMonthEnd)) continue;

                        $schedule = $data['schedule'] ?? [];
                        $personnelName = $data['personnel_name'] ?? 'Unknown';
                        $scheduleType = $data['type'] ?? 'midwife';

                        foreach ($schedule as $day => $timeSlots) {
                            $dayDate = $this->getDayDateForWeek($day, $data['week_start']);
                            if (!$dayDate) continue;

                            $timeSlotIndex = 0;
                            foreach ($timeSlots as $timeSlot) {
                                if (!empty($timeSlot)) {
                                    $scheduleData = [
                                        'id' => $doc->id() . '_' . $day . '_' . $timeSlotIndex,
                                        'title' => $personnelName . ' (' . ucfirst($scheduleType) . ')',
                                        'description' => $timeSlot,
                                        'type' => 'schedule',
                                        'personnel_name' => $personnelName,
                                        'schedule_type' => $scheduleType,
                                    ];

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

                            if (!isset($groupedItems[$appointmentDate])) {
                                $groupedItems[$appointmentDate] = [];
                            }
                            $groupedItems[$appointmentDate][] = $appointmentData;
                        }
                    }
                }
            }

            return response()->json([
                'groupedItems' => $groupedItems,
                'month' => $month
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch calendar data'], 500);
        }
    }
}
