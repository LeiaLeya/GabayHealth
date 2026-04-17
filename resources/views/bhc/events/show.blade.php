@extends('layouts.app')

@section('content')
<div class="container-fluid px-5 py-4">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h1 class="fw-bold mb-1 mb-0" style="font-size:2.5rem;">{{ $event['title'] }}</h1>
            <div class="lead text-muted mb-3" style="font-size:1.25rem;">{{ $event['description'] }}</div>
        </div>
        <a href="{{ route('events.index') }}" class="btn btn-outline-primary d-flex align-items-center gap-2 back-btn" style="min-width: 110px;">
            <i class="bi bi-arrow-left"></i> Back to Events
        </a>
    </div>

    <hr class="mb-4">

    <div class="row mb-4 g-3">
        <div class="col-auto d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt-fill text-primary"></i>
            <span class="fw-semibold">{{ $event['location'] }}</span>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <i class="bi bi-calendar-event-fill text-primary"></i>
            <span>
                {{ \Carbon\Carbon::parse($event['date'])->format('F d, Y') }}, 
                @if(isset($event['start_time']) && isset($event['end_time']))
                    {{ \Carbon\Carbon::parse($event['start_time'])->format('h:iA') }} - {{ \Carbon\Carbon::parse($event['end_time'])->format('h:iA') }}
                @elseif(isset($event['time']))
                    {{ $event['time'] }}
                @else
                    N/A
                @endif
            </span>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i>
            <span>Registered: {{ count($attendees) }}</span>
        </div>
        @if(isset($event['isOpenToAll']) && $event['isOpenToAll'])
            <div class="col-auto d-flex align-items-center gap-2">
                <i class="bi bi-globe text-primary"></i>
                <span>Open to All Barangays</span>
            </div>
        @endif
        @if(!empty($allowedBarangayNames ?? []))
            <div class="col-12 d-flex align-items-start gap-2">
                <i class="bi bi-geo-alt-fill text-primary"></i>
                <span>Allowed Barangays: {{ implode(', ', $allowedBarangayNames) }}</span>
            </div>
        @endif
        @if(isset($event['in_charge']) && $event['in_charge'])
            <div class="col-auto d-flex align-items-center gap-2">
                <i class="bi bi-person-badge-fill text-primary"></i>
                <span>In Charge: {{ $event['in_charge'] }}</span>
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold" style="font-size:1.2rem;">Attendees</div>
        <div class="d-flex gap-2">
            <a href="{{ route('events.exportPdf', $event['id']) }}" class="btn btn-sm btn-danger px-4">
                <i class="bi bi-filetype-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0" style="border-collapse:separate;border-spacing:0 0.25rem;">
            <thead class="table-light">
                <tr style="border-bottom:2px solid #e9ecef;">
                    <th class="fw-semibold" style="border:none;">Name</th>
                    <th class="fw-semibold" style="border:none;">Age</th>
                    <th class="fw-semibold" style="border:none;">Gender</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedAttendees as $attendee)
                    <tr style="border-bottom:1px solid #f1f1f1;">
                        <td style="border:none;">{{ $attendee['name'] }}</td>
                        <td style="border:none;">{{ $attendee['age'] ?? '-' }}</td>
                        <td style="border:none;">{{ $attendee['gender'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">No attendees yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $paginatedAttendees->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
.table th, .table td {
    vertical-align: middle;
    background: #fff;
    font-size: 1rem;
}
.table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}
.table tr {
    border-radius: 0.5rem;
}
.table tbody tr {
    border-top: none;
    border-bottom: 1px solid #f1f1f1;
}
.back-btn {
    transition: background 0.2s, color 0.2s, border 0.2s;
    font-size: 1.08rem;
    padding: 0.4rem 1.1rem;
    border-radius: 0.5rem;
}
.back-btn:hover, .back-btn:focus {
    background: #1657c1;
    color: #fff;
    border-color: #1657c1;
    text-decoration: none;
}
</style>
@endsection
