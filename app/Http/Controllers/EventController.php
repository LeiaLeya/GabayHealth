<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class EventController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $barangayId;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        $this->storage = $firebase->getStorage();
    }

    /**
     * Get all barangays that belong to the same RHU as the authenticated user.
     */
    private function getBarangaysWithinSameRhu(array $user): array
    {
        $barangays = [];

        try {
            $rhuId = null;

            if (($user['role'] ?? null) === 'barangay') {
                $barangayDoc = $this->firestore
                    ->collection('barangay')
                    ->document($user['id'])
                    ->snapshot();

                if ($barangayDoc->exists()) {
                    $barangayData = $barangayDoc->data();
                    $rhuId = $barangayData['rhuId'] ?? null;
                }
            } elseif (($user['role'] ?? null) === 'rhu') {
                $rhuId = $user['id'];
            }

            if ($rhuId) {
                $barangayDocs = $this->firestore
                    ->collection('barangay')
                    ->where('rhuId', '=', $rhuId)
                    ->documents();

                foreach ($barangayDocs as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();

                        if (($data['status'] ?? 'approved') !== 'approved') {
                            continue;
                        }

                        if (($user['role'] ?? null) === 'barangay' && $doc->id() === ($user['id'] ?? null)) {
                            continue;
                        }

                        $barangays[] = [
                            'id' => $doc->id(),
                            'name' => $data['healthCenterName']
                                ?? $data['barangay_name']
                                ?? $data['barangayName']
                                ?? $data['name']
                                ?? 'Barangay',
                        ];
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error('Failed to fetch barangays within RHU: ' . $th->getMessage());
        }

        return $barangays;
    }

    /**
     * Compute event status from date and time.
     * Respects manual "Cancelled" status if already set.
     */
    private function computeStatus(?string $date, ?string $startTime, ?string $endTime, ?string $existingStatus = null): string
    {
        if ($existingStatus === 'Cancelled') {
            return 'Cancelled';
        }

        if (!$date || !$startTime || !$endTime) {
            return 'Upcoming';
        }

        $startDateTime = Carbon::parse($date . ' ' . $startTime);
        $endDateTime = Carbon::parse($date . ' ' . $endTime);
        $now = Carbon::now();

        if ($now->lt($startDateTime)) {
            return 'Upcoming';
        }

        if ($now->between($startDateTime, $endDateTime)) {
            return 'Ongoing';
        }

        return 'Done';
    }

    public function index()
    {
        // Set timeout to prevent execution timeout
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access event management.');
        }
        
        // Initialize events as empty array
        $events = [];
        $barangaysWithinRhu = $this->getBarangaysWithinSameRhu($user);
        
        try {
            \Log::info('EventController - Fetching events for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            // Get events from user's sub-collection
            $eventsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('events')
                ->limit(50) // Limit results to prevent timeout
                ->documents();

            $count = 0;
            foreach ($eventsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    // Compute status dynamically unless cancelled
                    $data['status'] = $this->computeStatus(
                        $data['date'] ?? null,
                        $data['start_time'] ?? null,
                        $data['end_time'] ?? null,
                        $data['status'] ?? null
                    );
                    $events[] = array_merge($data, ['id' => $doc->id()]);
                    $count++;
                }
            }
            
            \Log::info('EventController - Found ' . $count . ' events');

            return view('pages.events.index', [
                'events' => $events,
                'barangaysWithinRhu' => $barangaysWithinRhu,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching events: ' . $e->getMessage());
            return view('pages.events.index', [
                'events' => $events,
                'barangaysWithinRhu' => $barangaysWithinRhu,
            ])->with('error', 'Error loading events data. Please try again.');
        }
    }

    public function show($id)
    {
        $user = session('user');
        $barangayId = $user['barangayId'] ?? null;

        if (!$barangayId) {
            return redirect()->route('events.index')->with('error', 'Barangay ID not found.');
        }

        $barangaysWithinRhu = $this->getBarangaysWithinSameRhu($user);
        $barangayNameMap = collect($barangaysWithinRhu)->pluck('name', 'id')->toArray();

        $eventDoc = $this->firestore
            ->collection("barangay/{$barangayId}/events")
            ->document($id)
            ->snapshot();

        if (!$eventDoc->exists()) {
            return redirect()->route('events.index')->with('error', 'Event not found.');
        }

        $event = array_merge($eventDoc->data(), ['id' => $id]);
        $allowedBarangayNames = $event['allowed_barangay_names'] ?? [];

        if (empty($allowedBarangayNames) && !empty($event['allowed_barangays'] ?? [])) {
            $allowedBarangayNames = collect($event['allowed_barangays'])
                ->filter(fn ($barangayId) => isset($barangayNameMap[$barangayId]))
                ->map(fn ($barangayId) => $barangayNameMap[$barangayId])
                ->values()
                ->all();
        }

        $attendeesQuery = $this->firestore
            ->collection("barangay/{$barangayId}/events/{$id}/attendees")
            ->documents();

        $attendees = [];
        foreach ($attendeesQuery as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                // Each doc represents one pre-registered attendee
                $attendees[] = [
                    'name' => $data['name'] ?? '',
                    'gender' => $data['gender'] ?? '',
                    'birthdate' => $data['birthdate'] ?? ($data['patient']['birthdate'] ?? ''),
                ];
            }
        }

        // Add computed age
        foreach ($attendees as &$attendee) {
            if (!empty($attendee['birthdate'])) {
                $attendee['age'] = Carbon::parse($attendee['birthdate'])->age;
            } else {
                $attendee['age'] = 'N/A';
            }
        }
        unset($attendee);

        // Paginate attendees (10 per page)
        $page = request()->get('page', 1);
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        $paginatedAttendees = new LengthAwarePaginator(
            array_slice($attendees, $offset, $perPage),
            count($attendees),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('pages.events.show', compact('event', 'attendees', 'paginatedAttendees', 'allowedBarangayNames'));
    }
    
    public function create()
    {
        return view('pages.events.create'); // You should have a Blade view for the form
    }
    
    public function store(Request $request)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access event management.');
        }
        
        // Get barangayId from user session
        $this->barangayId = $user['barangayId'] ?? null;
        
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'location' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string',
            'isOpenToAll' => 'nullable|boolean',
            'targetAttendees' => 'nullable|string|max:255',
            'in_charge' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'allowed_barangays' => 'nullable|array',
            'allowed_barangays.*' => 'string',
        ], [
            'end_time.after' => 'End time must be after start time.',
            'date.after_or_equal' => 'Event date cannot be in the past.',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $bucket = $this->storage->getBucket();
            $file = $request->file('image');
            $fileName = 'events/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $fileName]
            );
            $projectId = env('FIREBASE_PROJECT_ID');
            $imageUrl = "https://firebasestorage.googleapis.com/v0/b/{$projectId}.appspot.com/o/" . rawurlencode($fileName) . "?alt=media";
        }

        $computedStatus = $this->computeStatus($request->date, $request->start_time, $request->end_time, $request->status);

        $barangaysWithinRhu = $this->getBarangaysWithinSameRhu($user);
        $barangayMap = collect($barangaysWithinRhu)->pluck('name', 'id')->toArray();

        $allowedBarangays = $request->boolean('isOpenToAll')
            ? []
            : array_values(array_filter(
                array_unique($request->input('allowed_barangays', [])),
                fn ($barangayId) => isset($barangayMap[$barangayId])
            ));

        $allowedBarangayNames = array_map(fn ($id) => $barangayMap[$id], $allowedBarangays);

        $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->add([
                'title' => $request->title,
                'description' => $request->description,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'time' => Carbon::parse($request->start_time)->format('h:iA') . ' - ' . Carbon::parse($request->end_time)->format('h:iA'),
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $computedStatus,
                'isOpenToAll' => $request->boolean('isOpenToAll'),
                'targetAttendees' => $request->targetAttendees,
                'in_charge' => $request->in_charge,
                'image_url' => $imageUrl,
                'allowed_barangays' => $allowedBarangays,
                'allowed_barangay_names' => $allowedBarangayNames,
                'created_at' => now()->toDateTimeString(),
            ]);

        return redirect()->route('events.index')->with('success', 'Event created successfully!');
    }

    // GET: Edit event (for modal, returns event data)
    public function edit($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access event management.');
        }
        
        // Get barangayId from user session
        $this->barangayId = $user['barangayId'] ?? null;
        
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $eventDoc = $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->document($id)
            ->snapshot();

        if (!$eventDoc->exists()) {
            return redirect()->route('events.index')->with('error', 'Event not found.');
        }

        $event = array_merge($eventDoc->data(), ['id' => $id]);
        return response()->json($event);
    }

    // PUT: Update event
    public function update(Request $request, $id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access event management.');
        }
        
        // Get barangayId from user session
        $this->barangayId = $user['barangayId'] ?? null;
        
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'location' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string',
            'isOpenToAll' => 'nullable|boolean',
            'targetAttendees' => 'nullable|string|max:255',
            'in_charge' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'allowed_barangays' => 'nullable|array',
            'allowed_barangays.*' => 'string',
        ], [
            'end_time.after' => 'End time must be after start time.',
            'date.after_or_equal' => 'Event date cannot be in the past.',
        ]);

        $computedStatus = $this->computeStatus($request->date, $request->start_time, $request->end_time, $request->status);

        $barangaysWithinRhu = $this->getBarangaysWithinSameRhu($user);
        $barangayMap = collect($barangaysWithinRhu)->pluck('name', 'id')->toArray();

        $allowedBarangays = $request->boolean('isOpenToAll')
            ? []
            : array_values(array_filter(
                array_unique($request->input('allowed_barangays', [])),
                fn ($barangayId) => isset($barangayMap[$barangayId])
            ));

        $allowedBarangayNames = array_map(fn ($id) => $barangayMap[$id], $allowedBarangays);

        // Get existing event data to check if date/time changed (rescheduling)
        $existingEventDoc = $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->document($id)
            ->snapshot();

        $existingEvent = $existingEventDoc->exists() ? $existingEventDoc->data() : [];
        $existingDate = $existingEvent['date'] ?? null;
        $existingStartTime = $existingEvent['start_time'] ?? null;
        $existingEndTime = $existingEvent['end_time'] ?? null;
        $existingTime = $existingEvent['time'] ?? null;

        // Check if event is being rescheduled (date or time changed)
        $isRescheduled = false;
        if ($existingDate && $existingStartTime && $existingEndTime) {
            $dateChanged = $existingDate !== $request->date;
            $timeChanged = ($existingStartTime !== $request->start_time) || ($existingEndTime !== $request->end_time);
            $isRescheduled = $dateChanged || $timeChanged;
        }

        $eventData = [
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'time' => Carbon::parse($request->start_time)->format('h:iA') . ' - ' . Carbon::parse($request->end_time)->format('h:iA'),
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => $computedStatus,
            'isOpenToAll' => $request->boolean('isOpenToAll'),
            'targetAttendees' => $request->targetAttendees,
            'in_charge' => $request->in_charge,
            'allowed_barangays' => $allowedBarangays,
            'allowed_barangay_names' => $allowedBarangayNames,
            'updated_at' => now()->toDateTimeString(),
        ];

        // Add reschedule tracking if event was rescheduled
        if ($isRescheduled) {
            $currentRescheduleVersion = ($existingEvent['reschedule_version'] ?? 0) + 1;
            $eventData['rescheduled_at'] = now()->toDateTimeString();
            $eventData['reschedule_version'] = $currentRescheduleVersion;
            $eventData['previous_date'] = $existingDate;
            $eventData['previous_time'] = $existingTime;
        }

        if ($request->hasFile('image')) {
            $bucket = $this->storage->getBucket();
            $file = $request->file('image');
            $fileName = 'events/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $fileName]
            );
            $projectId = env('FIREBASE_PROJECT_ID');
            $imageUrl = "https://firebasestorage.googleapis.com/v0/b/{$projectId}.appspot.com/o/" . rawurlencode($fileName) . "?alt=media";
            $eventData['image_url'] = $imageUrl;
        }

        $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->document($id)
            ->set($eventData, ['merge' => true]);

        $successMessage = $isRescheduled 
            ? 'Event rescheduled successfully! Pre-registered attendees will be notified.' 
            : 'Event updated successfully!';

        return redirect()->route('events.index')->with('success', $successMessage);
    }


    // POST: Cancel event with reason
    public function cancel(Request $request, $id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to perform this action.');
        }
        
        $this->barangayId = $user['barangayId'] ?? null;
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->document($id)
            ->set([
                'status' => 'Cancelled',
                'cancellation_reason' => $request->reason,
                'cancelled_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ], ['merge' => true]);

        return redirect()->route('events.index')->with('success', 'Event has been cancelled.');
    }


    // Export attendees as PDF (attendance sheet)
    public function exportPdf($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access event management.');
        }
        
        $this->barangayId = $user['barangayId'] ?? null;
        
        if (!$this->barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $eventDoc = $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->document($id)
            ->snapshot();

        if (!$eventDoc->exists()) {
            return redirect()->route('events.index')->with('error', 'Event not found.');
        }

        $event = array_merge($eventDoc->data(), ['id' => $id]);

        $attendeesQuery = $this->firestore
            ->collection("barangay/{$this->barangayId}/events/{$id}/attendees")
            ->documents();

        $attendees = [];
        foreach ($attendeesQuery as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $attendees[] = [
                    'name' => $data['name'] ?? '',
                    'gender' => $data['gender'] ?? '',
                    'birthdate' => $data['birthdate'] ?? ($data['patient']['birthdate'] ?? ''),
                ];
            }
        }

        foreach ($attendees as &$attendee) {
            if (!empty($attendee['birthdate'])) {
                $attendee['age'] = Carbon::parse($attendee['birthdate'])->age;
            } else {
                $attendee['age'] = 'N/A';
            }
        }
        unset($attendee);

        // Render Blade to HTML
        $html = view('pages.events.attendees_pdf', [
            'event' => $event,
            'attendees' => $attendees,
        ])->render();

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'attendance_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $event['title'] ?? 'event') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
