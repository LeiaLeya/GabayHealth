<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index()
    {
        $rhuId = Session::get('user.id');
        if (!$rhuId) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        $db = $this->firestore->db;

        // Map BHU id -> readable name for this RHU
        $barangayMap = [];
        try {
            $bhuDocs = $db->collection('barangay')->where('rhuId', '=', $rhuId)->documents();
            foreach ($bhuDocs as $b) {
                if ($b->exists()) {
                    $d = $b->data();
                    $barangayMap[$b->id()] = $d['barangay'] ?? ($d['healthCenterName'] ?? $b->id());
                }
            }
        } catch (\Throwable $e) {}

        $notifications = [];
        try {
            $snap = $db->collection('rhu')->document($rhuId)->collection('notifications')->documents();
            foreach ($snap as $doc) {
                if (!$doc->exists()) continue;
                $data = $doc->data();

                // Normalize keys
                $data['createdAt']   = $data['created_at']   ?? ($data['createdAt']   ?? null);
                $data['barangayId']  = $data['barangay_id']  ?? ($data['barangayId']  ?? null);
                $data['barangayName']= $data['barangay_name']?? ($data['barangayName']?? null);

                if (!$data['barangayName'] && $data['barangayId'] && isset($barangayMap[$data['barangayId']])) {
                    $data['barangayName'] = $barangayMap[$data['barangayId']];
                }

                $data['isRead']       = (strtolower($data['status'] ?? '') === 'read') || !empty($data['read_at']);
                $data['statusLabel']  = $data['isRead'] ? 'Read' : 'New';
                $data['statusClass']  = $data['isRead'] ? 'bg-secondary' : 'bg-primary';
                $data['title']        = $data['title'] ?? match ($data['type'] ?? '') {
                    'barangay_registration' => 'New BHU registration',
                    'report_submitted'      => 'New report submitted',
                    default                 => 'Notification',
                };
                $data['createdAtHuman'] = $data['createdAt'] ? Carbon::parse($data['createdAt'])->diffForHumans() : '';

                $notifications[] = array_merge(['id' => $doc->id()], $data);
            }
        } catch (\Throwable $e) {
            // fail soft
        }

        // Unread first, then newest
        usort($notifications, function ($a, $b) {
            if (($a['isRead'] ?? false) !== ($b['isRead'] ?? false)) {
                return ($a['isRead'] ?? false) <=> ($b['isRead'] ?? false);
            }
            return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
        });

        return view('rhus.indexNotifications', compact('notifications'));
    }

    public function view($notificationId)
    {
        $rhuId = Session::get('user.id');
        if (!$rhuId) return redirect()->route('login')->with('error', 'Please log in to continue.');

        $ref = $this->firestore->db
            ->collection('rhu')->document($rhuId)
            ->collection('notifications')->document($notificationId);

        $doc = $ref->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('rhu.notifications')->with('error', 'Notification not found.');
        }

        $data = $doc->data();

        // Normalize for the view
        $notification = array_merge(['id' => $doc->id()], $data, [
            'created_at'   => $data['created_at']   ?? ($data['createdAt']   ?? null),
            'barangay_name'=> $data['barangay_name']?? ($data['barangayName']?? null),
            'barangay_id'  => $data['barangay_id']  ?? ($data['barangayId']  ?? null),
        ]);

        return view('rhus.viewNotification', compact('notification'));
    }

    public function markAsRead($notificationId)
    {
        $rhuId = Session::get('user.id');
        if (!$rhuId) return redirect()->route('login');

        $ref = $this->firestore->db
            ->collection('rhu')->document($rhuId)
            ->collection('notifications')->document($notificationId);

        $doc = $ref->snapshot();
        if (!$doc->exists()) return back()->with('error', 'Notification not found.');

        $ref->update([
            ['path' => 'status',  'value' => 'read'],
            ['path' => 'read_at', 'value' => now()->toDateTimeString()],
        ]);

        return back();
    }
}