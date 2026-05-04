<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Services\NotificationPageSupport;
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
        return redirect()->route('rhu.notifications.index');
    }

    private function fetchAll(string $rhuId): array
    {
        $all = [];
        try {
            $docs = $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->orderBy('createdAt', 'DESC')
                ->limit(200)
                ->documents();
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $all[] = array_merge($doc->data(), ['id' => $doc->id()]);
                }
            }
        } catch (\Exception $e) {
            // continue with empty
        }
        return $all;
    }

    private function fetchBarangays(string $rhuId): array
    {
        $barangays = [];
        try {
            $docs = $this->firestore
                ->collection('barangay')
                ->where('rhuId', '=', $rhuId)
                ->documents();
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $barangays[] = [
                        'id'   => $doc->id(),
                        'name' => $data['healthCenterName'] ?? ($data['location']['name'] ?? 'Unknown Barangay'),
                    ];
                }
            }
        } catch (\Exception $e) {
            // continue with empty
        }
        return $barangays;
    }

    public function inbox()
    {
        $user = session('user');
        if (!$user) return redirect()->route('login');

        $rhuId = $this->getBarangayId();
        if (!$rhuId) return redirect()->back()->with('error', 'RHU ID not found.');

        $all = $this->fetchAll($rhuId);
        [$inbox, $sent] = NotificationPageSupport::partitionInboxSent($all);
        $barangays = $this->fetchBarangays($rhuId);

        return $this->view('notifications.inbox', compact('inbox', 'sent', 'barangays'));
    }

    public function create()
    {
        $user = session('user');
        if (!$user) return redirect()->route('login');

        $rhuId = $this->getBarangayId();
        if (!$rhuId) return redirect()->back()->with('error', 'RHU ID not found.');

        $barangays = $this->fetchBarangays($rhuId);

        return $this->view('notifications.create', compact('barangays'));
    }

    public function sent()
    {
        $user = session('user');
        if (!$user) return redirect()->route('login');

        $rhuId = $this->getBarangayId();
        if (!$rhuId) return redirect()->back()->with('error', 'RHU ID not found.');

        $all = $this->fetchAll($rhuId);
        [, $sent] = NotificationPageSupport::partitionInboxSent($all);

        return $this->view('notifications.sent', compact('sent'));
    }

    public function markRead($id)
    {
        $user = session('user');
        if (!$user) return redirect()->route('login');

        $rhuId = $this->getBarangayId();
        if (!$rhuId) return response()->json(['error' => 'RHU ID not found.'], 400);

        try {
            $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->update([['path' => 'read', 'value' => true]]);
        } catch (\Exception $e) {
            // silently ignore
        }

        return response()->json(['ok' => true]);
    }

    public function store(Request $request)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to create notifications.');
        }

        $rhuId = $this->getBarangayId();

        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $request->validate([
            'notification_type' => 'required|in:health_alert,announcement,reminder,vaccination_update,clinic_schedule_update',
            'title'             => 'required|string|max:255',
            'message'           => 'required|string|max:2000',
            'target_audience'   => 'required|string',
            'target_barangays'  => 'required|array|min:1',
            'target_barangays.*'=> 'required|string',
            'target_purok'      => 'nullable|string|max:255',
            'target_age_group'  => 'nullable|string',
        ]);

        try {
            $targetBarangays = $request->input('target_barangays', []);

            // Resolve barangay names for display
            $barangayNames = [];
            foreach ($targetBarangays as $barangayId) {
                try {
                    $doc = $this->firestore->collection('barangay')->document($barangayId)->snapshot();
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $barangayNames[] = $data['healthCenterName'] ?? ($data['location']['name'] ?? $barangayId);
                    } else {
                        $barangayNames[] = $barangayId;
                    }
                } catch (\Exception $e) {
                    $barangayNames[] = $barangayId;
                }
            }

            $isScheduled = !empty($request->scheduled_at);
            $status = $isScheduled ? 'scheduled' : 'sent';

            $notificationData = [
                'notification_type'   => $request->notification_type,
                'title'               => $request->title,
                'message'             => $request->message,
                'target_audience'     => $request->target_audience,
                'target_purok'        => $request->target_purok,
                'target_age_group'    => $request->target_age_group,
                'target_barangays'    => $barangayNames,
                'target_barangay_ids' => $targetBarangays,
                'status'              => $status,
                'createdAt'           => now()->toDateTimeString(),
                'created_by'          => $user['id'],
                'created_by_name'     => $user['name'] ?? 'Health Worker',
            ];

            if ($isScheduled) {
                $notificationData['scheduled_at'] = $request->scheduled_at;
            } else {
                $notificationData['sent_at'] = now()->toDateTimeString();
            }

            // Save to RHU and capture the generated document ID
            $rhuNotifRef = $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->add($notificationData);

            // Push to each barangay, storing rhu_notification_id for cascading delete
            foreach ($targetBarangays as $barangayId) {
                $this->firestore
                    ->collection("barangay/{$barangayId}/notifications")
                    ->add(array_merge($notificationData, [
                        'from_rhu'            => $rhuId,
                        'source'              => 'rhu',
                        'rhu_notification_id' => $rhuNotifRef->id(),
                    ]));
            }

            $successMessage = $isScheduled
                ? 'Notification scheduled successfully!'
                : 'Notification sent successfully to ' . count($targetBarangays) . ' barangay(s)!';

            return redirect()->route('rhu.notifications.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create notification: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to delete notifications.');
        }

        $rhuId = $this->getBarangayId();

        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        try {
            // Read notification first to get target barangay IDs for cascading delete
            $notifDoc = $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->snapshot();

            if ($notifDoc->exists()) {
                $barangayIds = $notifDoc->data()['target_barangay_ids'] ?? [];
                foreach ($barangayIds as $barangayId) {
                    $matches = $this->firestore
                        ->collection("barangay/{$barangayId}/notifications")
                        ->where('rhu_notification_id', '=', $id)
                        ->documents();
                    foreach ($matches as $doc) {
                        if ($doc->exists()) {
                            $doc->reference()->delete();
                        }
                    }
                }
            }

            $this->firestore
                ->collection("rhu/{$rhuId}/notifications")
                ->document($id)
                ->delete();

            return redirect()->route('rhu.notifications.index')->with('success', 'Notification deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete notification: ' . $e->getMessage());
        }
    }
}
