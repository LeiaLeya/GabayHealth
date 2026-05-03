<?php

namespace App\Services;

use Carbon\Carbon;

class NotificationPageSupport
{
    public static function firestorePath(string $role, string $userId): string
    {
        if ($userId === '') {
            return '';
        }

        return match ($role) {
            'rhu' => "rhu/{$userId}/notifications",
            'barangay' => "barangay/{$userId}/notifications",
            default => '',
        };
    }

    /**
     * Notifications created via the compose form (broadcast to residents).
     *
     * @param  array<string, mixed>  $row
     */
    public static function isOutboundNotification(array $row): bool
    {
        return isset($row['notification_type']) && is_string($row['notification_type']);
    }

    /**
     * System / inbound items (e.g. barangay registration alerts for RHU).
     *
     * @param  array<string, mixed>  $row
     */
    public static function isInboundNotification(array $row): bool
    {
        return ! self::isOutboundNotification($row);
    }

    /**
     * Inbound docs under barangay/{id}/notifications meant for BHC staff (not resident copies).
     *
     * @param  array<string, mixed>  $row
     */
    public static function isBarangayStaffInboundNotification(array $row): bool
    {
        return ($row['type'] ?? '') === 'barangay_registration';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function sortTimestamp(array $row): float
    {
        foreach (['createdAt', 'created_at', 'sent_at', 'scheduled_at'] as $key) {
            $raw = $row[$key] ?? null;
            if ($raw !== null && $raw !== '' && is_scalar($raw)) {
                try {
                    return (float) Carbon::parse((string) $raw)->getTimestamp();
                } catch (\Throwable $e) {
                }
            }
        }

        return 0.0;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function inboxTitle(array $row): string
    {
        $type = $row['type'] ?? '';
        if ($type === 'barangay_registration') {
            return 'New barangay registration';
        }

        if (! empty($row['title'])) {
            return (string) $row['title'];
        }

        return 'Notification';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function inboxSubtitle(array $row): string
    {
        $type = $row['type'] ?? '';
        if ($type === 'barangay_registration') {
            return (string) ($row['barangay_name'] ?? '');
        }

        if (! empty($row['notification_type']) && ! empty($row['created_by_name'])) {
            return 'From '.(string) $row['created_by_name'];
        }

        return '';
    }

    /**
     * RHU-authored broadcast stored under barangay/{id}/notifications.
     *
     * @param  array<string, mixed>  $row
     */
    public static function linkedRhuMatchesRow(?string $rhuId, array $row): bool
    {
        if ($rhuId === null || $rhuId === '') {
            return false;
        }

        $createdBy = (string) ($row['created_by'] ?? '');
        $fromRhu = (string) ($row['from_rhu'] ?? '');

        return $createdBy === $rhuId || $fromRhu === $rhuId;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function isRhuBroadcastForBarangay(array $row, ?string $rhuId, string $barangayId): bool
    {
        if (! self::isOutboundNotification($row)) {
            return false;
        }

        if ((string) ($row['created_by'] ?? '') === $barangayId) {
            return false;
        }

        return self::linkedRhuMatchesRow($rhuId, $row);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function isBarangayOwnBroadcast(array $row, string $barangayId): bool
    {
        return self::isOutboundNotification($row)
            && (string) ($row['created_by'] ?? '') === $barangayId;
    }

    /**
     * @param  list<array<string, mixed>>  $all
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    public static function partitionBarangayInboxSent(array $all, string $barangayId, ?string $rhuId): array
    {
        $inbox = [];
        $sent = [];
        foreach ($all as $row) {
            if (self::isInboundNotification($row)) {
                if (self::isBarangayStaffInboundNotification($row)) {
                    $inbox[] = $row;
                }

                continue;
            }
            if (self::isRhuBroadcastForBarangay($row, $rhuId, $barangayId)) {
                $inbox[] = $row;

                continue;
            }
            if (self::isBarangayOwnBroadcast($row, $barangayId)) {
                $sent[] = $row;

                continue;
            }
        }

        return [self::sortNewestFirst($inbox), self::sortNewestFirst($sent)];
    }

    /**
     * Unread / needs attention in BHC inbox (system vs RHU broadcast).
     *
     * @param  array<string, mixed>  $row
     */
    public static function barangayInboxIsUnread(array $row, ?string $rhuId, string $barangayId): bool
    {
        if (self::isInboundNotification($row)) {
            return self::isBarangayStaffInboundNotification($row)
                && ($row['status'] ?? '') === 'unread';
        }

        if (self::isRhuBroadcastForBarangay($row, $rhuId, $barangayId)) {
            return empty($row['barangay_read_at']);
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function sortNewestFirst(array $rows): array
    {
        usort($rows, fn ($a, $b) => self::sortTimestamp($b) <=> self::sortTimestamp($a));

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $all
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    public static function partitionInboxSent(array $all): array
    {
        $inbox = [];
        $sent = [];
        foreach ($all as $row) {
            if (self::isInboundNotification($row)) {
                $inbox[] = $row;
            } else {
                $sent[] = $row;
            }
        }

        return [self::sortNewestFirst($inbox), self::sortNewestFirst($sent)];
    }
}
