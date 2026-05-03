<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use App\Services\NotificationBellService;
use App\Services\NotificationPageSupport;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class NotificationController extends Controller
{
    use HasRoleContext;

    protected $firestore;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
    }

    protected function notificationsPath(): string
    {
        $rhuId = $this->getBarangayId();

        return $rhuId ? "rhu/{$rhuId}/notifications" : '';
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function loadAllNotifications(): array
    {
        $path = $this->notificationsPath();
        if ($path === '') {
            return [];
        }

        $out = [];
        try {
            $snap = $this->firestore->collection($path)->limit(200)->documents();
            foreach ($snap as $doc) {
                if ($doc->exists()) {
                    $out[] = array_merge($doc->data(), ['id' => $doc->id()]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: '.$e->getMessage());
        }

        return NotificationPageSupport::sortNewestFirst($out);
    }

    public function inbox()
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to access notifications.');
        }

        if (! $this->getBarangayId()) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $all = $this->loadAllNotifications();
        [$inbox,] = NotificationPageSupport::partitionInboxSent($all);

        return $this->view('notifications.inbox', compact('inbox'));
    }

    public function create()
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to access notifications.');
        }

        if (! $this->getBarangayId()) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        return $this->view('notifications.create');
    }

    public function sent()
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to access notifications.');
        }

        if (! $this->getBarangayId()) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $all = $this->loadAllNotifications();
        [, $sent] = NotificationPageSupport::partitionInboxSent($all);

        return $this->view('notifications.sent', compact('sent'));
    }

    public function markRead($id)
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login.');
        }

        $rhuId = $this->getBarangayId();
        if (! $rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found.');
        }

        try {
            $doc = $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->snapshot();

            if (! $doc->exists()) {
                return redirect()->route('rhu.notifications.index')->with('error', 'Notification not found.');
            }

            $data = $doc->data();
            if (NotificationPageSupport::isOutboundNotification($data)) {
                return redirect()->route('rhu.notifications.index')->with('error', 'Invalid notification.');
            }

            $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'read'],
                    ['path' => 'read_at', 'value' => now()->toDateTimeString()],
                ]);

            NotificationBellService::forgetCacheForCurrentUser();

            return redirect()->route('rhu.notifications.index')->with('success', 'Marked as read.');
        } catch (\Exception $e) {
            \Log::error('Error marking notification read: '.$e->getMessage());

            return redirect()->back()->with('error', 'Could not update notification.');
        }
    }

    public function store(Request $request)
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to create notifications.');
        }

        $rhuId = $this->getBarangayId();

        if (! $rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $request->validate([
            'notification_type' => 'required|in:health_alert,announcement,reminder,vaccination_update,clinic_schedule_update',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'target_audience' => 'required|string',
            'target_purok' => 'nullable|string|max:255',
            'target_age_group' => 'nullable|string',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ]);

        try {
            $isScheduled = ! empty($request->scheduled_at);
            $status = $isScheduled ? 'scheduled' : 'sent';

            $notificationData = [
                'notification_type' => $request->notification_type,
                'title' => $request->title,
                'message' => $request->message,
                'target_audience' => $request->target_audience,
                'target_purok' => $request->target_purok,
                'target_age_group' => $request->target_age_group,
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
                ->collection("rhu/{$rhuId}/notifications")
                ->add($notificationData);

            NotificationBellService::forgetCacheForCurrentUser();

            $successMessage = $isScheduled
                ? 'Notification scheduled successfully!'
                : 'Notification sent successfully!';

            return redirect()->route('rhu.notifications.create')->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Error creating notification: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to create notification: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to delete notifications.');
        }

        $rhuId = $this->getBarangayId();

        if (! $rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        try {
            $doc = $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->snapshot();

            if ($doc->exists()) {
                $data = $doc->data();
                if (! NotificationPageSupport::isOutboundNotification($data)) {
                    return redirect()->route('rhu.notifications.sent')->with('error', 'Only sent broadcasts can be deleted here.');
                }
            }

            $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->delete();

            NotificationBellService::forgetCacheForCurrentUser();

            return redirect()->route('rhu.notifications.sent')->with('success', 'Notification deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to delete notification: '.$e->getMessage());
        }
    }
}
