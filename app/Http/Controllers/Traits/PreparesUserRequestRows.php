<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;

trait PreparesUserRequestRows
{
    /**
     * Firestore / mixed values that must be JSON-serializable and sortable as strings.
     */
    protected function scalarForUserRequest($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }
        if (is_object($value) && method_exists($value, 'get')) {
            try {
                $dt = $value->get();
                if ($dt instanceof \DateTimeInterface) {
                    return $dt->format('c');
                }
            } catch (\Throwable $e) {
            }
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareUserRequestRow(array $data, string $id): array
    {
        $row = array_merge($data, ['id' => $id]);
        foreach ($row as $key => $value) {
            $row[$key] = $this->scalarForUserRequest($value);
        }

        return $row;
    }

    /**
     * Best-effort "when was this entry created" for sorting (newest first).
     *
     * @param  array<string, mixed>  $row
     */
    protected function userRequestCreatedSortTimestamp(array $row): float
    {
        foreach (['submittedAt', 'createdAt', 'created_at', 'approved_at'] as $key) {
            if (empty($row[$key]) || ! is_scalar($row[$key])) {
                continue;
            }
            try {
                return (float) Carbon::parse((string) $row[$key])->getTimestamp();
            } catch (\Throwable $e) {
            }
        }

        return 0.0;
    }

    /**
     * Raw string (or scalar) for display — same field precedence as sorting.
     *
     * @param  array<string, mixed>  $row
     */
    protected function userRequestDisplayCreatedRaw(array $row): ?string
    {
        foreach (['submittedAt', 'createdAt', 'created_at', 'approved_at'] as $key) {
            if (! empty($row[$key]) && is_scalar($row[$key])) {
                return (string) $row[$key];
            }
        }

        return null;
    }

    /**
     * Case-insensitive substring match across common user-request fields.
     *
     * @param  array<string, mixed>  $row
     */
    protected function userRequestMatchesSearch(array $row, string $term): bool
    {
        $term = mb_strtolower(trim($term));
        if ($term === '') {
            return true;
        }

        $location = $row['location'] ?? null;
        $locationStr = is_string($location) ? $location : '';

        $parts = [
            $row['email'] ?? '',
            $row['username'] ?? '',
            $row['name'] ?? '',
            trim(((string) ($row['first_name'] ?? '')).' '.((string) ($row['last_name'] ?? ''))),
            $row['healthCenterName'] ?? '',
            $row['barangay'] ?? '',
            $row['userId'] ?? '',
            $row['uid'] ?? '',
            $row['id'] ?? '',
            $row['contact_number'] ?? '',
            $row['fullAddress'] ?? '',
            $row['address'] ?? '',
            $locationStr,
            $row['status'] ?? '',
            $row['registered_by_name'] ?? '',
        ];

        $haystack = mb_strtolower(implode(' ', array_map(static function ($p) {
            return is_scalar($p) ? (string) $p : '';
        }, $parts)));

        return str_contains($haystack, $term);
    }
}
