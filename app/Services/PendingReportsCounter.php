<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PendingReportsCounter
{
    public function __construct(
        private FirebaseService $firebase
    ) {}

    public function countForSidebar(): int
    {
        $user = session('user');
        if (!$user) {
            return 0;
        }

        $role = $user['role'] ?? null;
        if (!in_array($role, ['barangay', 'rhu'], true)) {
            return 0;
        }

        $uid = $user['id'] ?? $user['uid'] ?? null;
        if (!$uid) {
            return 0;
        }

        $cacheKey = $this->cacheKey($role, $uid);

        return (int) Cache::remember($cacheKey, now()->addSeconds(45), function () use ($uid) {
            return $this->countPendingForBarangayFilter($uid);
        });
    }

    public function forgetForSessionUser(): void
    {
        $user = session('user');
        if (!$user) {
            return;
        }

        $role = $user['role'] ?? null;
        $uid = $user['id'] ?? $user['uid'] ?? null;
        if (!$uid || !in_array($role, ['barangay', 'rhu'], true)) {
            return;
        }

        Cache::forget($this->cacheKey($role, $uid));
    }

    private function cacheKey(string $role, string $uid): string
    {
        return "sidebar_pending_reports:{$role}:{$uid}";
    }

    private function countPendingForBarangayFilter(string $barangayId): int
    {
        $count = 0;

        try {
            $firestore = $this->firebase->getFirestore();
            $documents = $firestore
                ->collection('reports')
                ->where('barangayId', '=', $barangayId)
                ->documents();

            foreach ($documents as $doc) {
                if (!$doc->exists()) {
                    continue;
                }
                $data = $doc->data();
                if (($data['status'] ?? '') === 'to be reviewed') {
                    $count++;
                }
            }
        } catch (\Throwable $e) {
            \Log::error('PendingReportsCounter: ' . $e->getMessage());

            return 0;
        }

        return $count;
    }
}
