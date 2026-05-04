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
                <button type="button" class="action-btn action-btn-delete" onclick="openDeleteModal()">
                    <i class="bi bi-trash3"></i> Delete Event
                </button>
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

.action-btn-delete {
    background: #fff;
    border: 1px solid #fca5a5;
    color: #dc2626;
    cursor: pointer;
    font-family: inherit;
}
.action-btn-delete:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* ── Delete modal ── */
.delete-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.delete-modal-overlay.open { display: flex; }
.delete-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 440px;
    padding: 28px 28px 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: modalIn .18s ease;
}
@keyframes modalIn {
    from { opacity:0; transform:translateY(-10px); }
    to   { opacity:1; transform:translateY(0); }
}
.delete-modal-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    background: #fee2e2;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #dc2626;
    margin-bottom: 14px;
}
.delete-modal-title {
    font-size: 1rem; font-weight: 700; color: #37352f; margin-bottom: 6px;
}
.delete-modal-body {
    font-size: .85rem; color: #787774; line-height: 1.6; margin-bottom: 18px;
}
.delete-modal-body strong { color: #37352f; }
.delete-modal-input {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e9e9e7;
    border-radius: 7px;
    font-size: .88rem;
    color: #37352f;
    outline: none;
    transition: border-color .15s;
    font-family: inherit;
    margin-bottom: 18px;
}
.delete-modal-input:focus { border-color: #dc2626; }
.delete-modal-input.match { border-color: #dc2626; background: #fff5f5; }
.delete-modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
}
.delete-modal-cancel {
    padding: 8px 20px; border-radius: 7px;
    border: 1px solid #e9e9e7; background: #fff;
    color: #37352f; font-size: .85rem; font-weight: 500;
    cursor: pointer; font-family: inherit;
    transition: background .15s;
}
.delete-modal-cancel:hover { background: #f7f7f5; }
.delete-modal-confirm {
    padding: 8px 20px; border-radius: 7px;
    border: none; background: #dc2626;
    color: #fff; font-size: .85rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    transition: background .15s, opacity .15s;
    opacity: .4; pointer-events: none;
}
.delete-modal-confirm.active { opacity: 1; pointer-events: auto; }
.delete-modal-confirm.active:hover { background: #b91c1c; }
</style>

{{-- Delete confirmation modal --}}
<div class="delete-modal-overlay" id="deleteModalOverlay">
    <div class="delete-modal">
        <div class="delete-modal-icon"><i class="bi bi-trash3-fill"></i></div>
        <div class="delete-modal-title">Delete Event</div>
        <div class="delete-modal-body">
            This action <strong>cannot be undone</strong>. This will permanently delete the event and all its attendee data.<br><br>
            Please type <strong id="deleteModalEventName"></strong> to confirm.
        </div>
        <input type="text" class="delete-modal-input" id="deleteConfirmInput"
               placeholder="Type the event name to confirm"
               oninput="checkDeleteMatch()" autocomplete="off">
        <div class="delete-modal-footer">
            <button type="button" class="delete-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteEventForm" method="POST" action="{{ route('rhu.events.destroy', $event['id']) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm_title" id="deleteConfirmHidden">
                <button type="submit" class="delete-modal-confirm" id="deleteConfirmBtn">
                    Delete Event
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const eventTitle = {{ Js::from($event['title']) }};

function openDeleteModal() {
    document.getElementById('deleteModalEventName').textContent = eventTitle;
    document.getElementById('deleteConfirmInput').value = '';
    document.getElementById('deleteConfirmBtn').classList.remove('active');
    document.getElementById('deleteModalOverlay').classList.add('open');
    setTimeout(() => document.getElementById('deleteConfirmInput').focus(), 100);
}

function closeDeleteModal() {
    document.getElementById('deleteModalOverlay').classList.remove('open');
}

function checkDeleteMatch() {
    const input = document.getElementById('deleteConfirmInput').value.trim().toLowerCase();
    const expected = eventTitle.trim().toLowerCase();
    const btn = document.getElementById('deleteConfirmBtn');
    const field = document.getElementById('deleteConfirmInput');
    if (input === expected) {
        btn.classList.add('active');
        field.classList.add('match');
        document.getElementById('deleteConfirmHidden').value = document.getElementById('deleteConfirmInput').value;
    } else {
        btn.classList.remove('active');
        field.classList.remove('match');
    }
}

// Close on overlay click
document.getElementById('deleteModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
@endsection
