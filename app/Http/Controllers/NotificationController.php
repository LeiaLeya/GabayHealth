<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    protected $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index()
    {
        $user = Session::get('user');
        $notifications = [];

        if ($user['role'] === 'rhu') {
            $notifications = $this->getAllNotifications();
        }

        return view('rhus.indexNotifications', compact('notifications'));
    }

    public function view($notificationId)
    {
        $user = Session::get('user');
        
        if ($user['role'] !== 'rhu') {
            return redirect()->route('rhu.notifications')->with('error', 'Unauthorized access');
        }

        $notification = $this->findNotificationById($notificationId);
        
        if (!$notification) {
            return redirect()->route('rhu.notifications')->with('error', 'Notification not found');
        }

        if ($notification['status'] === 'unread') {
            $this->markNotificationAsRead($notificationId);
            $notification['status'] = 'read'; 
        }

        return view('rhus.viewNotification', compact('notification'));
    }

    public function markAsRead($notificationId)
    {
        $user = Session::get('user');
        
        if ($user['role'] !== 'rhu') {
            return response()->json(['success' => false, 'error' => 'Unauthorized']);
        }

        $success = $this->markNotificationAsRead($notificationId);
        
        return response()->json(['success' => $success]);
    }

    private function findNotificationById($notificationId)
    {
        try {
            $rhuDocs = $this->firestore->db->collection('rhu')->documents();
            
            foreach ($rhuDocs as $rhuDoc) {
                if ($rhuDoc->exists()) {
                    $doc = $this->firestore->db->collection('rhu')
                        ->document($rhuDoc->id())
                        ->collection('notifications')
                        ->document($notificationId)
                        ->snapshot();
                        
                    if ($doc->exists()) {
                        $data = $doc->data();
                        return array_merge(['id' => $doc->id()], $data);
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error finding notification: ' . $e->getMessage());
        }

        return null;
    }

    private function getAllNotifications()
    {
        $allNotifications = [];

        try {
            $rhuDocs = $this->firestore->db->collection('rhu')->documents();
            
            foreach ($rhuDocs as $rhuDoc) {
                if ($rhuDoc->exists()) {
                    $notificationsQuery = $this->firestore->db->collection('rhu')
                        ->document($rhuDoc->id())
                        ->collection('notifications')
                        ->documents();

                    foreach ($notificationsQuery as $doc) {
                        if ($doc->exists()) {
                            $data = $doc->data();
                            $allNotifications[] = array_merge([
                                'id' => $doc->id(),
                                'rhu_id' => $rhuDoc->id()
                            ], $data);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());
        }

        return $allNotifications;
    }

    private function markNotificationAsRead($notificationId)
    {
        try {
            $rhuDocs = $this->firestore->db->collection('rhu')->documents();
            
            foreach ($rhuDocs as $rhuDoc) {
                if ($rhuDoc->exists()) {
                    $doc = $this->firestore->db->collection('rhu')
                        ->document($rhuDoc->id())
                        ->collection('notifications')
                        ->document($notificationId)
                        ->snapshot();
                        
                    if ($doc->exists()) {
                        $this->firestore->db->collection('rhu')
                            ->document($rhuDoc->id())
                            ->collection('notifications')
                            ->document($notificationId)
                            ->update([
                                ['path' => 'status', 'value' => 'read'],
                                ['path' => 'read_at', 'value' => now()->toDateTimeString()]
                            ]);
                        
                        return true;
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
        }

        return false;
    }
}