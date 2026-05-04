@extends('layouts.app')

@section('content')
<div style="max-width:900px;margin:0 auto;padding:32px 24px;">

    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:0;">
                @php
                    $status = $event['status'] ?? 'Upcoming';
                    $statusColor = match($status) {
                        'Done'      => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'Ongoing'   => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'Cancelled' => ['bg'=>'#fee2e2','color'=>'#991b1b'],
                        default     => ['bg'=>'#f3f4f6','color'=>'#374151'],
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="font-size:.65rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;background:{{ $statusColor['bg'] }};color:{{ $statusColor['color'] }};padding:3px 10px;border-radius:20px;">
                        {{ $status }}
                    </span>
                </div>
                <h1 style="font-size:1.75rem;font-weight:700;color:#37352f;margin:0 0 6px;">{{ $event['title'] }}</h1>
                @if(!empty($event['description']))
                    <p style="font-size:.9rem;color:#787774;margin:0;line-height:1.6;">{{ $event['description'] }}</p>
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;align-items:flex-start;flex-wrap:wrap;">
                <a href="{{ route('rhu.calendars.index') }}" class="action-btn action-btn-ghost">
                    <i class="bi bi-calendar3"></i> View in Calendar
                </a>
                <a href="{{ route('events.index') }}" class="action-btn action-btn-ghost">
                    <i class="bi bi-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>
    </div>

    {{-- Info card --}}
    <div class="notion-card" style="margin-bottom:20px;">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div>
                    <div class="info-label">Location</div>
                    <div class="info-value">{{ $event['location'] ?? '—' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-calendar-event-fill"></i></div>
                <div>
                    <div class="info-label">Date &amp; Time</div>
                    <div class="info-value">
                        {{ \Carbon\Carbon::parse($event['date'])->format('F d, Y') }}
                        @if(isset($event['start_time']) && isset($event['end_time']))
                            &mdash; {{ \Carbon\Carbon::parse($event['start_time'])->format('h:i A') }} – {{ \Carbon\Carbon::parse($event['end_time'])->format('h:i A') }}
                        @elseif(isset($event['time']))
                            &mdash; {{ $event['time'] }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="info-label">Registered</div>
                    <div class="info-value">{{ count($attendees) }} attendee{{ count($attendees) !== 1 ? 's' : '' }}</div>
                </div>
            </div>
            @if(isset($event['in_charge']) && $event['in_charge'])
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <div class="info-label">In Charge</div>
                    <div class="info-value">{{ $event['in_charge'] }}</div>
                </div>
            </div>
            @endif
            @if(isset($event['isOpenToAll']) && $event['isOpenToAll'])
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-globe"></i></div>
                <div>
                    <div class="info-label">Access</div>
                    <div class="info-value">Open to All Barangays</div>
                </div>
            </div>
            @elseif(!empty($allowedBarangayNames ?? []))
            <div class="info-item" style="grid-column:1/-1;">
                <div class="info-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="info-label">Allowed Barangays</div>
                    <div class="info-value">{{ implode(', ', $allowedBarangayNames) }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Attendees --}}
    <div class="notion-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div>
                <div style="font-size:.65rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#9b9b9b;margin-bottom:3px;">Attendees</div>
                <div style="font-size:1rem;font-weight:600;color:#37352f;">{{ count($attendees) }} registered</div>
            </div>
            <a href="{{ route('events.exportPdf', $event['id']) }}" class="action-btn action-btn-danger">
                <i class="bi bi-filetype-pdf"></i> Export PDF
            </a>
        </div>

        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width:50%;">Name</th>
                        <th style="width:20%;">Age</th>
                        <th style="width:30%;">Gender</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedAttendees as $attendee)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="attendee-avatar">{{ strtoupper(substr($attendee['name'] ?? '?', 0, 1)) }}</div>
                                    <span style="font-weight:500;color:#37352f;">{{ $attendee['name'] }}</span>
                                </div>
                            </td>
                            <td style="color:#787774;">{{ $attendee['age'] ?? '—' }}</td>
                            <td>
                                <span class="gender-chip {{ strtolower($attendee['gender'] ?? '') }}">
                                    {{ $attendee['gender'] ?? '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div style="text-align:center;padding:40px 20px;color:#9b9b9b;">
                                    <i class="bi bi-person-x" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                                    No attendees registered yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedAttendees->hasPages())
        <div style="margin-top:16px;display:flex;justify-content:center;">
            {{ $paginatedAttendees->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

<style>
*, *::before, *::after { box-sizing: border-box; }

.notion-card {
    background: #fff;
    border: 1px solid #e9e9e7;
    border-radius: 8px;
    padding: 20px 24px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px 20px;
}
.info-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.info-icon {
    width: 32px; height: 32px;
    border-radius: 6px;
    background: #f1f1ef;
    color: #1657c1;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
    flex-shrink: 0;
    margin-top: 2px;
}
.info-label {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: #9b9b9b;
    margin-bottom: 2px;
}
.info-value {
    font-size: .88rem;
    font-weight: 500;
    color: #37352f;
    line-height: 1.4;
}

/* ── Buttons ── */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .8rem;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all .15s;
    white-space: nowrap;
}
.action-btn-ghost {
    background: #fff;
    border-color: #e9e9e7;
    color: #37352f;
}
.action-btn-ghost:hover {
    background: #f1f1ef;
    color: #37352f;
    text-decoration: none;
}
.action-btn-danger {
    background: #fff;
    border-color: #fca5a5;
    color: #dc2626;
}
.action-btn-danger:hover {
    background: #fef2f2;
    color: #dc2626;
    text-decoration: none;
}

/* ── Table ── */
.inv-table-wrap {
    border: 1px solid #e9e9e7;
    border-radius: 6px;
    overflow: hidden;
}
.inv-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .875rem;
}
.inv-table thead th {
    background: #f7f7f5;
    padding: 9px 14px;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: #787774;
    border-bottom: 1px solid #e9e9e7;
    text-align: left;
}
.inv-table tbody td {
    padding: 11px 14px;
    border-bottom: 1px solid #f1f1ef;
    vertical-align: middle;
}
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody tr:hover { background: #fafaf9; }

.attendee-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: #dbeafe;
    color: #1657c1;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.gender-chip {
    display: inline-block;
    font-size: .72rem;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 20px;
    background: #f1f1ef;
    color: #787774;
}
.gender-chip.male   { background: #dbeafe; color: #1e40af; }
.gender-chip.female { background: #fce7f3; color: #9d174d; }
</style>
@endsection
