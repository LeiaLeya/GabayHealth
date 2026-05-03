<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class NotificationBellService
{
    public function __construct(
        protected FirebaseService $firebase
    ) {}

    /**
     * @return array{notificationInboxUnreadCount: int, notificationBellItems: list<array{title: string, url: string, subtitle: string}>, notificationInboxUrl: string|null}
     */
    public function composeData(): array
    {
        $user = session('user');
        $role = $user['role'] ?? null;
        if (! in_array($role, ['rhu', 'barangay'], true)) {
            return [
                'notificationInboxUnreadCount' => 0,
                'notificationBellItems' => [],
                'notificationInboxUrl' => null,
            ];
        }

        $prefix = $role === 'rhu' ? 'rhu' : 'bhc';
        $inboxUrl = route("{$prefix}.notifications.index");

        $userId = $user['id'] ?? '';
        $cacheKey = "notif_bell_{$role}_{$userId}";

        $snapshot = Cache::remember($cacheKey, 25, function () use ($role, $userId) {
            return $this->loadUnreadPreview($role, $userId);
        });

        return [
            'notificationInboxUnreadCount' => $snapshot['count'],
            'notificationBellItems' => $snapshot['items'],
            'notificationInboxUrl' => $inboxUrl,
        ];
    }

    public static function forgetCacheForCurrentUser(): void
    {
        $user = session('user');
        if (! $user) {
            return;
        }
        $role = $user['role'] ?? '';
        $userId = $user['id'] ?? '';
        Cache::forget("notif_bell_{$role}_{$userId}");
    }

    /**
     * @return array{count: int, items: list<array{title: string, url: string, subtitle: string}>}
     */
    protected function loadUnreadPreview(string $role, string $userId): array
    {
        if ($role === 'barangay') {
            return $this->loadBarangayUnreadPreview($userId);
        }

        $path = NotificationPageSupport::firestorePath($role, $userId);
        if ($path === '') {
            return ['count' => 0, 'items' => []];
        }

        $firestore = $this->firebase->getFirestore();
        $rows = [];

        try {
            $query = $firestore->collection($path)
                ->where('status', '==', 'unread')
                ->limit(80)
                ->documents();

            foreach ($query as $doc) {
                if (! $doc->exists()) {
                    continue;
                }
                $row = array_merge($doc->data(), ['id' => $doc->id()]);
                if (NotificationPageSupport::isOutboundNotification($row)) {
                    continue;
                }
                $rows[] = $row;
            }
        } catch (\Throwable $e) {
            \Log::warning('Notification bell query failed, using scan fallback: '.$e->getMessage());
            try {
                foreach ($firestore->collection($path)->limit(120)->documents() as $doc) {
                    if (! $doc->exists()) {
                        continue;
                    }
                    $row = array_merge($doc->data(), ['id' => $doc->id()]);
                    if (NotificationPageSupport::isOutboundNotification($row)) {
                        continue;
                    }
                    if (($row['status'] ?? '') !== 'unread') {
                        continue;
                    }
                    $rows[] = $row;
                }
            } catch (\Throwable $e2) {
                \Log::warning('Notification bell fallback failed: '.$e2->getMessage());
            }
        }

        return $this->buildBellPayloadFromRows($rows, 'rhu.notifications.index');
    }

    protected function barangayLinkedRhuId(string $barangayId): ?string
    {
        try {
            $doc = $this->firebase->getFirestore()->collection('barangay')->document($barangayId)->snapshot();
            if ($doc->exists()) {
                $r = $doc->data()['rhuId'] ?? null;

                return ($r !== null && $r !== '') ? (string) $r : null;
            }
        } catch (\Throwable $e) {
            \Log::warning('Notification bell: could not read barangay rhuId: '.$e->getMessage());
        }

        return null;
    }

    /**
     * BHC: unread system inbox + RHU broadcasts not yet acknowledged (barangay_read_at).
     *
     * @return array{count: int, items: list<array{title: string, url: string, subtitle: string}>}
     */
    protected function loadBarangayUnreadPreview(string $barangayId): array
    {
        $path = NotificationPageSupport::firestorePath('barangay', $barangayId);
        if ($path === '') {
            return ['count' => 0, 'items' => []];
        }

        $rhuId = $this->barangayLinkedRhuId($barangayId);
        $firestore = $this->firebase->getFirestore();
        $eligible = [];

        try {
            foreach ($firestore->collection($path)->limit(150)->documents() as $doc) {
                if (! $doc->exists()) {
                    continue;
                }
                $row = array_merge($doc->data(), ['id' => $doc->id()]);
                if (NotificationPageSupport::barangayInboxIsUnread($row, $rhuId, $barangayId)) {
                    $eligible[] = $row;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Notification bell barangay scan failed: '.$e->getMessage());
        }

        return $this->buildBellPayloadFromRows($eligible, 'bhc.notifications.index');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{count: int, items: list<array{title: string, url: string, subtitle: string}>}
     */
    protected function buildBellPayloadFromRows(array $rows, string $inboxRouteName): array
    {
        $rows = NotificationPageSupport::sortNewestFirst($rows);
        $count = count($rows);
        $items = [];
        foreach (array_slice($rows, 0, 6) as $row) {
            $items[] = [
                'title' => NotificationPageSupport::inboxTitle($row),
                'subtitle' => NotificationPageSupport::inboxSubtitle($row),
                'url' => route($inboxRouteName),
            ];
        }

        return ['count' => $count, 'items' => $items];
    }
}
