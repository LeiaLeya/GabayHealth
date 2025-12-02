<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Carbon\Carbon;

class NotificationController extends Controller
{
    use HasRoleContext;

    protected $firestore;
    protected $storage;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        $this->storage = $firebase->getStorage();
    }

    public function index()
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access notifications.');
        }
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $notifications = [];
        
        try {
            $notificationsQuery = $this->firestore
                ->collection("barangay/{$barangayId}/notifications")
                ->orderBy('createdAt', 'DESC')
                ->limit(100)
                ->documents();

            foreach ($notificationsQuery as $doc) {
                if ($doc->exists()) {
                    $notifications[] = array_merge($doc->data(), ['id' => $doc->id()]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());
        }

        return $this->view('notifications.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to create notifications.');
        }
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        $request->validate([
            'notification_type' => 'required|in:health_alert,announcement,reminder,vaccination_update,clinic_schedule_update',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'target_audience' => 'required|string',
            'target_purok' => 'nullable|string|max:255',
            'target_age_group' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ]);

        try {
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $bucket = $this->storage->getBucket();
                $file = $request->file('image');
                $fileName = 'notifications/' . uniqid() . '.' . $file->getClientOriginalExtension();
                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    ['name' => $fileName]
                );
                $projectId = env('FIREBASE_PROJECT_ID');
                $imageUrl = "https://firebasestorage.googleapis.com/v0/b/{$projectId}.appspot.com/o/" . rawurlencode($fileName) . "?alt=media";
            }

            $isScheduled = !empty($request->scheduled_at);
            $status = $isScheduled ? 'scheduled' : 'sent';

            $notificationData = [
                'notification_type' => $request->notification_type,
                'title' => $request->title,
                'message' => $request->message,
                'target_audience' => $request->target_audience,
                'target_purok' => $request->target_purok,
                'target_age_group' => $request->target_age_group,
                'image_url' => $imageUrl,
                'status' => $status,
                'createdAt' => now()->toDateTimeString(),
                'created_by' => $user['id'],
                'created_by_name' => $user['name'] ?? 'Health Worker',
            ];

            if ($isScheduled) {
                $notificationData['scheduled_at'] = $request->scheduled_at;
            } else {
                $notificationData['sent_at'] = now()->toDateTimeString();
            }

            $this->firestore
                ->collection("barangay/{$barangayId}/notifications")
                ->add($notificationData);

            $successMessage = $isScheduled 
                ? 'Notification scheduled successfully!' 
                : 'Notification sent successfully!';

            return redirect()->route('rhu.notifications.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Error creating notification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create notification: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to delete notifications.');
        }
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found. Please contact administrator.');
        }

        try {
            $this->firestore
                ->collection("barangay/{$barangayId}/notifications")
                ->document($id)
                ->delete();

            return redirect()->route('rhu.notifications.index')->with('success', 'Notification deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete notification: ' . $e->getMessage());
        }
    }
}


