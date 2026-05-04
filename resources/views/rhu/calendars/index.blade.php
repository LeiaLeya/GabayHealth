@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    <!-- Flash Toasts -->
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert" id="successToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert" id="errorToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Calendar</h2>
            <p class="text-muted mb-0 small">View and manage events, schedules, and appointments</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="cal-nav-group d-flex align-items-center gap-2">
                <button class="btn btn-icon" id="prevMonth" title="Previous Month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="cal-month-label" id="currentMonthDisplay">{{ \Carbon\Carbon::parse($currentMonth)->format('F Y') }}</span>
                <button class="btn btn-icon" id="nextMonth" title="Next Month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <button class="btn btn-today" id="todayBtn">Today</button>
            <button class="btn btn-add-schedule" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-lg me-1"></i>Add Schedule
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="bi bi-calendar-event-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="totalEvents">0</div>
                    <div class="stat-label">Total Events</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="totalAppointments">0</div>
                    <div class="stat-label">Appointments</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-amber">
                <div class="stat-icon"><i class="bi bi-calendar2-week-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="totalSchedules">0</div>
                    <div class="stat-label">Schedules</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="bi bi-calendar3-week-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="thisWeekEvents">0</div>
                    <div class="stat-label">This Week</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="cal-card">
        <div class="cal-grid cal-head-row">
            <div class="cal-head weekend">Sun</div>
            <div class="cal-head">Mon</div>
            <div class="cal-head">Tue</div>
            <div class="cal-head">Wed</div>
            <div class="cal-head">Thu</div>
            <div class="cal-head">Fri</div>
            <div class="cal-head weekend">Sat</div>
        </div>
        <div class="cal-grid cal-body" id="calendarBody">
            <div class="cal-loading-wrap">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="cal-legend mt-3">
        <span class="legend-dot event"></span><span class="legend-text">Event</span>
        <span class="legend-dot appointment"></span><span class="legend-text">Appointment</span>
        <span class="legend-dot schedule"></span><span class="legend-text">Schedule</span>
        <span class="legend-dot done"></span><span class="legend-text">Done</span>
    </div>
</div>

<!-- ───────────────────────── Event Details Modal ───────────────────────── -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom" id="eventModalHeader">
                <div>
                    <div class="modal-type-badge" id="eventModalBadge">Event</div>
                    <h5 class="modal-title-custom" id="eventModalTitleText">Event Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="eventModalBody"></div>
            <div class="modal-footer border-0 pt-0 pb-3 px-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ───────────────────────── Add Schedule Modal ───────────────────────── -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom">
                <div>
                    <div class="modal-type-badge">New Schedule</div>
                    <h5 class="modal-title-custom">Add Weekly Schedule</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('rhu.schedules.store') }}" method="POST" id="scheduleForm" novalidate class="d-flex flex-column flex-grow-1 overflow-hidden">
                @csrf

                <div class="modal-body p-4">

                    {{-- Validation errors --}}
                    @if($errors->any())
                    <div class="alert alert-danger rounded-3 py-2 mb-4 small">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- ── Location + Personnel ── --}}
                    <div class="form-section mb-3">
                        <div class="form-section-title">
                            <i class="bi bi-geo-alt me-2"></i>Location &amp; Personnel
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="calendarAddBarangay">Location</label>
                                <select class="form-select rounded-3 {{ $errors->has('barangay_id') ? 'is-invalid' : '' }}"
                                        name="barangay_id" id="calendarAddBarangay" required>
                                    <option value="">— Select location —</option>
                                    @foreach($barangayOptions as $option)
                                        <option value="{{ $option['id'] }}"
                                                {{ old('barangay_id', '') === $option['id'] ? 'selected' : '' }}>
                                            {{ $option['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">{{ $errors->first('barangay_id') ?: 'Please select a location.' }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="personnelSelect">Staff Member</label>
                                @php
                                    $allPersonnel = [];
                                    foreach(($availableMidwives ?? []) as $p) {
                                        $n = $p['full_name'] ?? $p['fullName'] ?? $p['name'] ?? trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? ''));
                                        if ($n && ($p['id'] ?? '')) $allPersonnel[] = ['id'=>$p['id'],'name'=>$n,'type'=>'midwife','desig'=>ucfirst($p['designation'] ?? $p['role'] ?? 'Midwife')];
                                    }
                                    foreach(($assignedDoctors ?? []) as $p) {
                                        $n = $p['full_name'] ?? $p['fullName'] ?? $p['name'] ?? trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? ''));
                                        if ($n && ($p['id'] ?? '')) $allPersonnel[] = ['id'=>$p['id'],'name'=>$n,'type'=>'doctor','desig'=>ucfirst($p['designation'] ?? $p['role'] ?? 'Doctor')];
                                    }
                                @endphp
                                <select id="personnelSelect"
                                        class="form-select rounded-3 {{ $errors->has('personnel_id') || $errors->has('type') ? 'is-invalid' : '' }}"
                                        onchange="fillPersonnel(this)"
                                        required>
                                    <option value="">— Select staff member —</option>
                                    @foreach($allPersonnel as $p)
                                        <option value="{{ $p['id'] }}"
                                                data-name="{{ $p['name'] }}"
                                                data-type="{{ $p['type'] }}"
                                                data-desig="{{ $p['desig'] }}"
                                                {{ old('personnel_id') === $p['id'] ? 'selected' : '' }}>
                                            {{ $p['name'] }} ({{ $p['desig'] }})
                                        </option>
                                    @endforeach
                                    @if(empty($allPersonnel))
                                        <option disabled>No staff found — check personnel settings</option>
                                    @endif
                                </select>
                                <input type="hidden" name="personnel_id"   id="personnelId"   value="{{ old('personnel_id') }}">
                                <input type="hidden" name="personnel_name" id="personnelName" value="{{ old('personnel_name') }}">
                                <input type="hidden" name="type"           id="personnelType" value="{{ old('type') }}">
                                <div class="invalid-feedback">Please select a staff member.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Designation</label>
                                <input type="text" class="form-control rounded-3 bg-light"
                                       id="personnelDesignation" placeholder="Auto-filled" readonly
                                       value="{{ old('type') ? ucfirst(old('type')) : '' }}">
                            </div>
                        </div>
                    </div>

                    {{-- ── Week Period ── --}}
                    <div class="form-section mb-3">
                        <div class="form-section-title">
                            <i class="bi bi-calendar-range me-2"></i>Week Period
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="weekStart">Start Date</label>
                                <input type="date" class="form-control rounded-3 {{ $errors->has('week_start') ? 'is-invalid' : '' }}"
                                       name="week_start" id="weekStart"
                                       value="{{ old('week_start') }}" required>
                                <div class="invalid-feedback">{{ $errors->first('week_start') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="weekEnd">End Date</label>
                                <input type="date" class="form-control rounded-3 {{ $errors->has('week_end') ? 'is-invalid' : '' }}"
                                       name="week_end" id="weekEnd"
                                       value="{{ old('week_end') }}" required>
                                <div class="invalid-feedback">{{ $errors->first('week_end') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Time Slots ── --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-clock me-2"></i>Time Slots
                            <span class="text-muted fw-normal ms-1" style="text-transform:none;letter-spacing:0;font-size:.75rem;">
                                — leave blank for days off
                            </span>
                        </div>
                        <div class="schedule-days">
                            @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat','sunday'=>'Sun'] as $day => $abbr)
                            <div class="sched-day-row">
                                <div class="sched-day-label">
                                    <span class="day-abbr {{ in_array($day, ['saturday','sunday']) ? 'weekend' : '' }}">{{ $abbr }}</span>
                                    <span class="day-full">{{ ucfirst($day) }}</span>
                                </div>
                                <div class="sched-day-slots flex-grow-1" id="timeSlots_{{ $day }}">
                                    <div class="time-input-group">
                                        <input type="time" class="form-control form-control-sm start-time"
                                               data-day="{{ $day }}" onchange="updateFormattedTime(this)">
                                        <span class="time-sep">→</span>
                                        <input type="time" class="form-control form-control-sm end-time"
                                               data-day="{{ $day }}" onchange="updateFormattedTime(this)">
                                        <button type="button" class="btn btn-icon-sm btn-remove" onclick="removeTimeSlot(this)" title="Remove slot">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <input type="hidden" name="schedule[{{ $day }}][]" class="formatted-time" value="">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-icon-sm btn-add-slot"
                                        onclick="addTimeSlot('{{ $day }}')" title="Add time slot">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div><!-- /modal-body -->

                <div class="modal-footer border-top px-4 py-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="scheduleSubmitBtn">
                        <span class="submit-label"><i class="bi bi-check2 me-1"></i>Create Schedule</span>
                        <span class="submit-loading d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>Saving…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Auto-reopen modal if validation failed --}}
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('addScheduleModal')).show();
    var oldType = '{{ old("type") }}';
    if (oldType) {
        document.getElementById('personnelDesignation').value =
            oldType.charAt(0).toUpperCase() + oldType.slice(1);
    }
});
</script>
@endif

<style>
*, *::before, *::after { box-sizing: border-box; }

/* ─── Nav ─── */
.cal-nav-group {
    background: transparent; border: 1px solid #e9e9e7;
    border-radius: 6px; padding: 3px 6px;
}
.btn-icon {
    background: none; border: none; color: #9b9b9b;
    padding: 4px 8px; border-radius: 4px; cursor: pointer;
    transition: background .1s, color .1s; line-height: 1;
}
.btn-icon:hover { background: #f1f1ef; color: #37352f; }
.cal-month-label {
    font-weight: 600; font-size: .95rem; color: #37352f;
    min-width: 130px; text-align: center;
}
.btn-today {
    background: transparent; border: 1px solid #e9e9e7; color: #787774;
    font-size: .82rem; font-weight: 500; padding: 5px 14px;
    border-radius: 6px; transition: all .1s;
}
.btn-today:hover { background: #f1f1ef; color: #37352f; }
.btn-add-schedule {
    background: #37352f; border: none;
    color: #fff; font-size: .82rem; font-weight: 500;
    padding: 6px 16px; border-radius: 6px;
    transition: opacity .1s;
}
.btn-add-schedule:hover { opacity: .82; }

/* ─── Stats ─── */
.stat-card {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: 6px;
    background: #fff; border: 1px solid #e9e9e7;
}
.stat-icon {
    width: 36px; height: 36px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0; background: #f1f1ef; color: #787774;
}
.stat-blue .stat-icon, .stat-green .stat-icon,
.stat-amber .stat-icon, .stat-purple .stat-icon { background: #f1f1ef; color: #787774; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #37352f; line-height: 1; }
.stat-label { font-size: .72rem; color: #9b9b9b; font-weight: 400; margin-top: 2px; }

/* ─── Calendar card ─── */
.cal-card {
    background: #fff; border-radius: 6px;
    border: 1px solid #e9e9e7; overflow: hidden;
}
.cal-grid { display: grid; grid-template-columns: repeat(7,1fr); }
.cal-head-row { background: #fff; border-bottom: 1px solid #e9e9e7; }
.cal-head {
    padding: .6rem .5rem; text-align: center;
    font-size: .7rem; font-weight: 500;
    color: #9b9b9b; letter-spacing: .3px; text-transform: uppercase;
}
.cal-head.weekend { color: #c4c4c2; }
.cal-body { min-height: 560px; }
.cal-loading-wrap {
    grid-column: 1/-1; display: flex;
    align-items: center; justify-content: center; min-height: 400px;
}

/* ─── Day cell ─── */
.cal-day {
    min-height: 115px; padding: .5rem .5rem;
    border-right: 1px solid #f1f1ef; border-bottom: 1px solid #f1f1ef;
    background: #fff; transition: background .1s; position: relative;
}
.cal-day:hover { background: #fafaf9; }
.cal-day:nth-child(7n) { border-right: none; }
.cal-day.weekend-col { background: #fafaf9; }
.cal-day.weekend-col:hover { background: #f5f5f3; }
.cal-day.other-month { background: #fafaf9; }
.cal-day.today { background: #fff !important; z-index: 1; }
.cal-day-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; font-size: .8rem; font-weight: 400;
    color: #37352f; border-radius: 4px; margin-bottom: .3rem;
}
.cal-day.today .cal-day-num { background: #eb5757; color: #fff; font-weight: 600; border-radius: 4px; }
.cal-day.other-month .cal-day-num { color: #c4c4c2; }

/* ─── Event chips ─── */
.cal-events { display: flex; flex-direction: column; gap: 2px; }
.cal-chip {
    font-size: .69rem; font-weight: 500; padding: 2px 6px;
    border-radius: 3px; cursor: pointer; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
    transition: filter .1s;
    border: none; text-align: left; display: block; width: 100%; line-height: 1.5;
}
.cal-chip:hover { filter: brightness(.94); }
.cal-chip.event       { background: #dbeafe; color: #1e40af; }
.cal-chip.appointment { background: #dcfce7; color: #166534; }
.cal-chip.schedule    { background: #fef9c3; color: #854d0e; }
.cal-chip.done        { background: #f1f1ef; color: #9b9b9b; text-decoration: line-through; }

/* ─── Legend ─── */
.cal-legend { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.legend-dot { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 4px; }
.legend-dot.event       { background: #3b82f6; }
.legend-dot.appointment { background: #22c55e; }
.legend-dot.schedule    { background: #eab308; }
.legend-dot.done        { background: #c4c4c2; }
.legend-text { font-size: .74rem; color: #9b9b9b; font-weight: 400; }

/* ─── Modal header ─── */
.modal-header-custom {
    display: flex; align-items: flex-start; justify-content: space-between;
    background: #37352f; padding: 18px 22px;
}
.modal-type-badge {
    display: inline-block; font-size: .64rem; font-weight: 600;
    letter-spacing: .6px; text-transform: uppercase;
    background: rgba(255,255,255,.12); color: rgba(255,255,255,.75);
    padding: 2px 8px; border-radius: 4px; margin-bottom: 5px;
}
.modal-title-custom { font-size: 1rem; font-weight: 600; color: #fff; margin: 0; }

/* ─── Event detail rows ─── */
.detail-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 9px 0; border-bottom: 1px solid #f1f1ef;
}
.detail-row:last-child { border-bottom: none; }
.detail-icon {
    width: 30px; height: 30px; border-radius: 5px;
    background: #f1f1ef; display: flex; align-items: center; justify-content: center;
    color: #787774; font-size: .9rem; flex-shrink: 0; margin-top: 1px;
}
.detail-label { font-size: .65rem; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; color: #9b9b9b; margin-bottom: 2px; }
.detail-value { font-size: .88rem; font-weight: 500; color: #37352f; margin: 0; }

/* ─── Schedule form ─── */
.form-section { background: #fafaf9; border-radius: 6px; padding: 14px 16px; border: 1px solid #f1f1ef; }
.form-section-title {
    font-size: .7rem; font-weight: 600; letter-spacing: .4px;
    text-transform: uppercase; color: #787774; margin-bottom: 12px;
}
.schedule-days { display: flex; flex-direction: column; gap: 4px; }
.sched-day-row {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 7px 10px; background: #fff;
    border: 1px solid #e9e9e7; border-radius: 6px;
}
.sched-day-label {
    display: flex; flex-direction: column;
    align-items: center; min-width: 36px; padding-top: 3px;
}
.day-abbr {
    font-size: .68rem; font-weight: 700; letter-spacing: .3px; text-transform: uppercase;
    color: #787774; background: #f1f1ef; width: 30px; height: 30px;
    border-radius: 5px; display: flex; align-items: center; justify-content: center;
}
.day-abbr.weekend { color: #9b9b9b; background: #f1f1ef; }
.day-full { font-size: .62rem; color: #c4c4c2; margin-top: 3px; }
.sched-day-slots { display: flex; flex-direction: column; gap: 5px; padding-top: 2px; }
.time-input-group { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; }
.time-input-group .form-control-sm {
    max-width: 118px; border-radius: 5px; border-color: #e9e9e7; font-size: .82rem;
}
.time-sep { color: #c4c4c2; font-size: .9rem; flex-shrink: 0; }
.btn-icon-sm {
    width: 26px; height: 26px; border-radius: 5px; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; cursor: pointer; flex-shrink: 0; transition: background .1s;
}
.btn-remove { background: #f1f1ef; color: #9b9b9b; }
.btn-remove:hover { background: #e9e9e7; color: #eb5757; }
.btn-add-slot { background: #f1f1ef; color: #787774; align-self: center; margin-top: 2px; }
.btn-add-slot:hover { background: #e9e9e7; color: #37352f; }

@media (max-width:768px) {
    .cal-day { min-height: 70px; padding: .35rem .3rem; }
    .cal-day-num { width: 22px; height: 22px; font-size: .72rem; }
    .cal-chip { font-size: .62rem; padding: 2px 4px; }
    .cal-month-label { min-width: 110px; font-size: .9rem; }
    .btn-add-schedule { padding: 5px 12px; font-size: .8rem; }
}
</style>

<script>
let currentMonth = '{{ $currentMonth ?? now()->format("Y-m") }}';
let groupedItems  = @json($groupedItems);
let serverToday   = '{{ now()->format("Y-m-d") }}';

/* ── Time helpers ── */
function formatTime(t) {
    if (!t) return '';
    try {
        const [h,m] = t.split(':').map(Number);
        if (isNaN(h)||isNaN(m)) return t;
        const p = h>=12?'PM':'AM', d = h===0?12:h>12?h-12:h;
        return `${d}:${String(m).padStart(2,'0')} ${p}`;
    } catch { return t; }
}
function formatDate(ds) {
    if (!ds) return '';
    try {
        const d=new Date(ds); if(isNaN(d))return ds;
        return d.toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
    } catch { return ds; }
}
function formatTimeRange(s,e,raw) {
    if (s&&e) return `${formatTime(s)} – ${formatTime(e)}`;
    if (raw&&raw.includes(' - ')) { const [a,b]=raw.split(' - '); return `${formatTime(a.trim())} – ${formatTime(b.trim())}`; }
    return raw||'';
}

/* ── Stats ── */
function updateStats() {
    let ev=0,ap=0,sc=0,wk=0;
    const today=new Date(), ws=new Date(today), we=new Date(today);
    ws.setDate(today.getDate()-today.getDay()); we.setDate(ws.getDate()+6);
    for (const date in groupedItems) {
        const d=new Date(date);
        groupedItems[date].forEach(i=>{
            if(i.type==='event')ev++;
            else if(i.type==='appointment')ap++;
            else if(i.type==='schedule')sc++;
            if(d>=ws&&d<=we)wk++;
        });
    }
    document.getElementById('totalEvents').textContent       = ev;
    document.getElementById('totalAppointments').textContent = ap;
    document.getElementById('totalSchedules').textContent    = sc;
    document.getElementById('thisWeekEvents').textContent    = wk;
}

/* ── Calendar ── */
function generateCalendar() {
    if (!currentMonth||currentMonth==='null') currentMonth=new Date().toISOString().slice(0,7);
    const start=new Date(currentMonth+'-01');
    const last=new Date(start.getFullYear(),start.getMonth()+1,0);
    const fdow=start.getDay(), total=last.getDate();
    const weeks=Math.max(Math.ceil((fdow+total)/7),5);
    const firstCell=new Date(start.getFullYear(),start.getMonth(),1-fdow);
    let html='';
    for(let w=0;w<weeks;w++){
        for(let d=0;d<7;d++){
            const c=new Date(firstCell); c.setDate(firstCell.getDate()+w*7+d);
            const ds=c.getFullYear()+'-'+String(c.getMonth()+1).padStart(2,'0')+'-'+String(c.getDate()).padStart(2,'0');
            const isCur=c.getMonth()===start.getMonth();
            const isWknd=d===0||d===6, isToday=ds===serverToday;
            let cls='cal-day';
            if(!isCur)cls+=' other-month';
            if(isWknd&&isCur)cls+=' weekend-col';
            if(isToday)cls+=' today';
            const num=`<div class="cal-day-num">${c.getDate()}</div>`;
            const evs=isCur?`<div class="cal-events">${buildChips(ds)}</div>`:'';
            html+=`<div class="${cls}">${num}${evs}</div>`;
        }
    }
    document.getElementById('calendarBody').innerHTML=html;
    updateStats();
}

function buildChips(ds){
    const items=groupedItems[ds];
    if(!items||!items.length)return '';
    return items.map(item=>{
        let cls=`cal-chip ${item.type}`;
        if(item.status==='Done')cls+=' done';
        const esc=t=>t.replace(/'/g,"\\'").replace(/"/g,'\\"');
        const label=item.title.length>22?item.title.slice(0,22)+'…':item.title;
        const click=item.type==='event'
            ?`window.location.href='/events/${esc(item.id)}'`
            :`showDetail('${esc(item.id)}','${item.type}')`;
        return `<button class="${cls}" onclick="${click}" title="${esc(item.title)}">${label}</button>`;
    }).join('');
}

/* ── Event detail modal ── */
function showDetail(id,type){
    let item=null;
    for(const d in groupedItems){item=groupedItems[d].find(e=>e.id===id&&e.type===type);if(item)break;}
    if(!item)return;
    const hdr=document.getElementById('eventModalHeader');
    const badge=document.getElementById('eventModalBadge');
    const title=document.getElementById('eventModalTitleText');
    const body=document.getElementById('eventModalBody');
    hdr.style.background='#37352f';
    badge.textContent=type.charAt(0).toUpperCase()+type.slice(1);
    title.textContent=item.title;
    const row=(icon,label,val)=>val?`<div class="detail-row"><div class="detail-icon"><i class="bi bi-${icon}"></i></div><div><div class="detail-label">${label}</div><p class="detail-value">${val}</p></div></div>`:'';
    let html='';
    if(type==='event'){
        html+=row('calendar-event','Event',item.title);
        html+=row('clock','Date & Time',formatDate(item.date)+(item.start_time&&item.end_time?', '+formatTimeRange(item.start_time,item.end_time):''));
        html+=row('geo-alt','Venue',item.location);
        html+=row('person-badge','In Charge',item.in_charge);
        html+=row('info-circle','Status',item.status);
        html+=row('card-text','Description',item.description);
    } else if(type==='appointment'){
        html+=row('person-check','Patient',item.title);
        html+=row('clock','Time',formatTimeRange(item.start_time,item.end_time,item.time));
        html+=row('briefcase','Service',item.description);
        if(item.patient&&item.patient.age)html+=row('person','Patient Info',`Age: ${item.patient.age}, Gender: ${item.patient.gender}`);
        html+=row('chat-text','Notes',item.notes||'-');
    } else if(type==='schedule'){
        html+=row('person-badge','Personnel',item.title);
        html+=row('clock','Time',item.description);
    }
    body.innerHTML=html;
    new bootstrap.Modal(document.getElementById('eventModal')).show();
}

/* ── Month navigation ── */
function updateMonthDisplay(){
    const d=new Date(currentMonth+'-01');
    document.getElementById('currentMonthDisplay').textContent=
        d.toLocaleDateString('en-US',{month:'long',year:'numeric'});
}
function loadMonthData(month){
    document.getElementById('calendarBody').innerHTML='<div class="cal-loading-wrap"><div class="spinner-border text-primary" role="status"></div></div>';
    fetch(`data?month=${month}`)
        .then(r=>{if(!r.ok)throw new Error(r.status);return r.json();})
        .then(data=>{
            if(data.error)throw new Error(data.error);
            groupedItems=data.groupedItems||data;
            currentMonth=month; updateMonthDisplay(); generateCalendar();
        })
        .catch(()=>{currentMonth=month;updateMonthDisplay();generateCalendar();});
}
document.getElementById('prevMonth').addEventListener('click',()=>{
    const d=new Date(currentMonth+'-01');d.setMonth(d.getMonth()-1);
    loadMonthData(d.toISOString().slice(0,7));
});
document.getElementById('nextMonth').addEventListener('click',()=>{
    const d=new Date(currentMonth+'-01');d.setMonth(d.getMonth()+1);
    loadMonthData(d.toISOString().slice(0,7));
});
document.getElementById('todayBtn').addEventListener('click',()=>{
    loadMonthData(serverToday.slice(0,7));
});

/* ── Personnel select ── */
function fillPersonnel(sel){
    if(!sel.value){
        ['personnelId','personnelName','personnelType'].forEach(id=>{document.getElementById(id).value='';});
        document.getElementById('personnelDesignation').value='';
        return;
    }
    const opt=sel.options[sel.selectedIndex];
    document.getElementById('personnelId').value    = sel.value;
    document.getElementById('personnelName').value  = opt.dataset.name  || '';
    document.getElementById('personnelType').value  = opt.dataset.type  || '';
    document.getElementById('personnelDesignation').value = opt.dataset.desig || '';
    sel.classList.remove('is-invalid');
}

/* ── Schedule time slots ── */
function updateFormattedTime(input){
    const group=input.closest('.time-input-group');
    const s=group.querySelector('.start-time').value;
    const e=group.querySelector('.end-time').value;
    group.querySelector('.formatted-time').value = s&&e ? `${s} - ${e}` : '';
}
function addTimeSlot(day){
    const container=document.getElementById(`timeSlots_${day}`);
    const div=document.createElement('div');
    div.className='time-input-group';
    div.innerHTML=`
        <input type="time" class="form-control form-control-sm start-time" data-day="${day}" onchange="updateFormattedTime(this)">
        <span class="time-sep">→</span>
        <input type="time" class="form-control form-control-sm end-time" data-day="${day}" onchange="updateFormattedTime(this)">
        <button type="button" class="btn btn-icon-sm btn-remove" onclick="removeTimeSlot(this)" title="Remove slot">
            <i class="bi bi-x-lg"></i>
        </button>
        <input type="hidden" name="schedule[${day}][]" class="formatted-time" value="">`;
    container.appendChild(div);
}
function removeTimeSlot(btn){
    const group=btn.closest('.time-input-group');
    const container=group.closest('.sched-day-slots');
    if(container.querySelectorAll('.time-input-group').length>1)group.remove();
}

/* ── Default week dates ── */
function setDefaultWeekDates(){
    const today=new Date();
    const mon=new Date(today); mon.setDate(today.getDate()-today.getDay()+1);
    const sun=new Date(mon);   sun.setDate(mon.getDate()+6);
    const fmt=d=>d.toISOString().split('T')[0];
    const ws=document.getElementById('weekStart'); if(ws&&!ws.value)ws.value=fmt(mon);
    const we=document.getElementById('weekEnd');   if(we&&!we.value)we.value=fmt(sun);
}

/* ── Form submission ── */
document.getElementById('scheduleForm').addEventListener('submit', function(e){
    // Sync all formatted-time hidden inputs
    document.querySelectorAll('.time-input-group').forEach(group=>{
        const s=group.querySelector('.start-time')?.value;
        const en=group.querySelector('.end-time')?.value;
        const h=group.querySelector('.formatted-time');
        if(h) h.value=s&&en?`${s} - ${en}`:'';
    });

    // Validate personnel
    const typeVal=document.getElementById('personnelType').value;
    const idVal  =document.getElementById('personnelId').value;
    if(!typeVal||!idVal){
        e.preventDefault();
        const sel=document.getElementById('personnelSelect');
        sel.classList.add('is-invalid');
        sel.focus();
        return;
    }

    // Validate location
    const loc=document.getElementById('calendarAddBarangay');
    if(!loc.value){
        e.preventDefault();
        loc.classList.add('is-invalid');
        loc.focus();
        return;
    }

    // Loading state
    const lbl=document.querySelector('#scheduleSubmitBtn .submit-label');
    const spn=document.querySelector('#scheduleSubmitBtn .submit-loading');
    if(lbl)lbl.classList.add('d-none');
    if(spn)spn.classList.remove('d-none');
    document.getElementById('scheduleSubmitBtn').disabled=true;
});

/* ── Init ── */
document.addEventListener('DOMContentLoaded', ()=>{
    loadMonthData(currentMonth);
    setDefaultWeekDates();
    document.querySelectorAll('.toast').forEach(t=>{
        setTimeout(()=>{ bootstrap.Toast.getOrCreateInstance(t).hide(); }, 4000);
    });
});
</script>
@endsection
