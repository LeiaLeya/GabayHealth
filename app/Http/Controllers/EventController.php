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

    // ✅ GET: Show list of events
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
                    $events[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    $count++;
                }
            }
            
            \Log::info('EventController - Found ' . $count . ' events');

            return view('pages.events.index', compact('events'));
        } catch (\Exception $e) {
            \Log::error('Error fetching events: ' . $e->getMessage());
            return view('pages.events.index', compact('events'))->with('error', 'Error loading events data. Please try again.');
        }
    }

    // ✅ GET: Show event details and attendees
    public function show($id)
    {
        $user = session('user');
        $barangayId = $user['barangayId'] ?? null;

        if (!$barangayId) {
            return redirect()->route('events.index')->with('error', 'Barangay ID not found.');
        }

        $eventDoc = $this->firestore
            ->collection("barangay/{$barangayId}/events")
            ->document($id)
            ->snapshot();

        if (!$eventDoc->exists()) {
            return redirect()->route('events.index')->with('error', 'Event not found.');
        }

        $event = array_merge($eventDoc->data(), ['id' => $id]);

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

        return view('pages.events.show', compact('event', 'attendees', 'paginatedAttendees'));
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
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'location' => 'required|string',
            'status' => 'nullable|string',
            'isOpenToAll' => 'nullable|boolean',
            'targetAttendees' => 'nullable|string|max:255',
            'in_charge' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ], [
            'end_time.after' => 'End time must be after start time.',
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
                'status' => $request->status ?? 'Upcoming',
                'isOpenToAll' => $request->boolean('isOpenToAll'),
                'targetAttendees' => $request->targetAttendees,
                'in_charge' => $request->in_charge,
                'image_url' => $imageUrl,
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
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'location' => 'required|string',
            'status' => 'nullable|string',
            'isOpenToAll' => 'nullable|boolean',
            'targetAttendees' => 'nullable|string|max:255',
            'in_charge' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ], [
            'end_time.after' => 'End time must be after start time.',
        ]);

        $eventData = [
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'time' => Carbon::parse($request->start_time)->format('h:iA') . ' - ' . Carbon::parse($request->end_time)->format('h:iA'),
            'location' => $request->location,
            'status' => $request->status ?? 'Upcoming',
            'isOpenToAll' => $request->boolean('isOpenToAll'),
            'targetAttendees' => $request->targetAttendees,
            'in_charge' => $request->in_charge,
            'updated_at' => now()->toDateTimeString(),
        ];

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

        return redirect()->route('events.index')->with('success', 'Event updated successfully!');
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
