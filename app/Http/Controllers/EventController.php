<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class EventController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $barangayId;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        $this->storage = $firebase->getStorage();
        // Use logged-in user's barangay ID if available
        $this->barangayId = session('user.id', 'sZK52EtUl22SSCKzSPIM');
    }

    // ✅ GET: Show list of events
    public function index()
    {
        $eventsQuery = $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->documents();

        $events = [];
        foreach ($eventsQuery as $doc) {
            if ($doc->exists()) {
                $events[] = array_merge($doc->data(), ['id' => $doc->id()]);
            }
        }

        return view('pages.events.index', compact('events'));
    }

    // ✅ GET: Show event details and attendees
    public function show($id)
    {
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
            $data = $doc->data();
            if (isset($data['patients']) && is_array($data['patients'])) {
                foreach ($data['patients'] as $patient) {
                    $attendees[] = [
                        'name' => $patient['name'] ?? '',
                        'gender' => $patient['gender'] ?? '',
                        'birthdate' => $patient['birthdate'] ?? '',
                    ];
                }
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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'required|string',
            'status' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
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
                'time' => $request->time,
                'location' => $request->location,
                'status' => $request->status ?? 'Upcoming',
                'image_url' => $imageUrl,
                'created_at' => now()->toDateTimeString(),
            ]);

        return redirect()->route('events.index')->with('success', 'Event created successfully!');
    }

    // GET: Edit event (for modal, returns event data)
    public function edit($id)
    {
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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'required|string',
            'status' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $eventData = [
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'status' => $request->status ?? 'Upcoming',
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

    // Export attendees as CSV
    public function exportCsv($id)
    {
        $eventDoc = $this->firestore
            ->collection("barangay/{$this->barangayId}/events")
            ->document($id)
            ->snapshot();

        if (!$eventDoc->exists()) {
            return redirect()->route('events.index')->with('error', 'Event not found.');
        }

        $attendeesQuery = $this->firestore
            ->collection("barangay/{$this->barangayId}/events/{$id}/attendees")
            ->documents();

        $attendees = [];
        foreach ($attendeesQuery as $doc) {
            $data = $doc->data();
            if (isset($data['patients']) && is_array($data['patients'])) {
                foreach ($data['patients'] as $patient) {
                    $attendees[] = [
                        'name' => $patient['name'] ?? '',
                        'gender' => $patient['gender'] ?? '',
                        'birthdate' => $patient['birthdate'] ?? '',
                    ];
                }
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

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendees.csv"',
        ];

        $callback = function() use ($attendees) {
            $file = fopen('php://output', 'w');
            // CSV header
            fputcsv($file, ['Name', 'Age', 'Gender']);
            foreach ($attendees as $attendee) {
                fputcsv($file, [$attendee['name'], $attendee['age'], $attendee['gender']]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
