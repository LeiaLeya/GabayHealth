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
            <div class="modal-footer border-0 pt-0 pb-3 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <a href="#" id="eventModalActionLink" class="btn btn-dark rounded-pill px-4 d-none" target="_self">
                    <i class="bi bi-arrow-right me-1"></i><span id="eventModalActionLabel">View</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ───────────────────────── Add Schedule Modal ───────────────────────── -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">

            <div class="sm-header">
                <div>
                    <div class="sm-eyebrow" id="smEyebrow">New Schedule</div>
                    <h5 class="sm-title" id="smTitle">Add Weekly Schedule</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="schedForm" method="POST" novalidate>
                @csrf
                <div id="smMethodField"></div>
                <input type="hidden" name="barangay_id"    id="smBarangayId">
                <input type="hidden" name="type"           id="smType">
                <input type="hidden" name="personnel_id"   id="smPersonnelId">
                <input type="hidden" name="personnel_name" id="smPersonnelName">
                <input type="hidden" name="week_start"     id="smWeekStart">
                <input type="hidden" name="week_end"       id="smWeekEnd">

                <div class="modal-body p-0">

                    {{-- ── Section 1: Location + Type ── --}}
                    <div class="sm-section sm-section-top">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="sm-label">Location</label>
                                <select class="form-select form-select-sm rounded-3" id="smLocation">
                                    <option value="">— Select location —</option>
                                    @foreach($barangayOptions as $opt)
                                        <option value="{{ $opt['id'] }}" {{ ($opt['id'] === ($selectedBarangayId ?? '')) ? 'selected' : '' }}>{{ $opt['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="sm-label">Staff Role</label>
                                <div class="type-toggle" id="typeToggle">
                                    <button type="button" class="type-btn" id="typeBtnMidwife" onclick="selectType('midwife')">
                                        <i class="bi bi-heart-pulse-fill"></i> Midwife
                                    </button>
                                    <button type="button" class="type-btn" id="typeBtnDoctor" onclick="selectType('doctor')">
                                        <i class="bi bi-person-vcard-fill"></i> Doctor
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 2: Personnel picker ── --}}
                    <div class="sm-section sm-section-personnel">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="sm-label mb-0">Personnel</label>
                            <span class="sm-hint" id="smPersonnelHint">Select a schedule type first</span>
                        </div>
                        <div class="personnel-scroll" id="personnelScroll">
                            <div class="personnel-placeholder">
                                <i class="bi bi-people"></i>
                                <span>Select a schedule type to see available personnel</span>
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 3: Week + Shift Grid ── --}}
                    <div class="sm-section">
                        <div class="sm-grid-header">
                            <label class="sm-label mb-0">Weekly Schedule</label>
                            <div class="week-nav">
                                <button type="button" class="wk-btn" onclick="shiftWeek(-1)"><i class="bi bi-chevron-left"></i></button>
                                <span class="wk-label" id="wkLabel">—</span>
                                <button type="button" class="wk-btn" onclick="shiftWeek(1)"><i class="bi bi-chevron-right"></i></button>
                            </div>
                            <div class="quick-actions">
                                <button type="button" class="qa-btn" onclick="applyPattern('weekday-am')" title="Set Morning for Mon–Fri">Weekday AM</button>
                                <button type="button" class="qa-btn" onclick="applyPattern('weekday-pm')" title="Set Afternoon for Mon–Fri">Weekday PM</button>
                                <button type="button" class="qa-btn" onclick="applyPattern('full-week')" title="Morning + Afternoon Mon–Fri">Full Weekdays</button>
                                <button type="button" class="qa-btn qa-btn-danger" onclick="applyPattern('clear')">Clear All</button>
                            </div>
                        </div>

                        <div class="shift-grid-wrap">
                            <table class="shift-grid">
                                <thead>
                                    <tr>
                                        <th class="sg-row-header"></th>
                                        <th>Mon</th>
                                        <th>Tue</th>
                                        <th>Wed</th>
                                        <th>Thu</th>
                                        <th>Fri</th>
                                        <th class="sg-weekend">Sat</th>
                                        <th class="sg-weekend">Sun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="sg-row-header">
                                            <div class="sg-shift-name morning-label">Morning</div>
                                            <div class="sg-shift-time">8:00 AM – 12:00 PM</div>
                                        </td>
                                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                                        <td class="{{ in_array($day, ['saturday','sunday']) ? 'sg-weekend' : '' }}">
                                            <button type="button" class="sg-cell" data-day="{{ $day }}" data-shift="morning" onclick="toggleCell(this)">
                                                <i class="bi bi-check2 sg-check"></i>
                                            </button>
                                        </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td class="sg-row-header">
                                            <div class="sg-shift-name afternoon-label">Afternoon</div>
                                            <div class="sg-shift-time">1:00 PM – 5:00 PM</div>
                                        </td>
                                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                                        <td class="{{ in_array($day, ['saturday','sunday']) ? 'sg-weekend' : '' }}">
                                            <button type="button" class="sg-cell" data-day="{{ $day }}" data-shift="afternoon" onclick="toggleCell(this)">
                                                <i class="bi bi-check2 sg-check"></i>
                                            </button>
                                        </td>
                                        @endforeach
                                    </tr>
                                    <tr class="sg-custom-row">
                                        <td class="sg-row-header">
                                            <div class="sg-shift-name" style="color:#6b7280;">Custom</div>
                                            <div class="sg-shift-time">specific hours</div>
                                        </td>
                                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                                        <td class="sg-custom-cell {{ in_array($day, ['saturday','sunday']) ? 'sg-weekend' : '' }}" id="customCell_{{ $day }}">
                                            <button type="button" class="sg-add-custom" onclick="addCustomSlot('{{ $day }}')" title="Add custom time">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                            <div class="custom-slots-list" id="customSlots_{{ $day }}"></div>
                                        </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="sg-legend">
                            <span class="sg-legend-item"><span class="sg-legend-dot morning-dot"></span>Morning shift</span>
                            <span class="sg-legend-item"><span class="sg-legend-dot afternoon-dot"></span>Afternoon shift</span>
                            <span class="sg-legend-item text-muted">Leave cells empty for days off</span>
                        </div>
                    </div>

                </div>{{-- /modal-body --}}

                <div class="modal-footer border-top px-4 py-3 gap-2 justify-content-between">
                    <div id="smValidationMsg" class="text-danger small d-none"><i class="bi bi-exclamation-circle me-1"></i><span id="smValidationText"></span></div>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="smSubmitBtn">
                            <span class="sm-submit-label"><i class="bi bi-check2 me-1"></i><span id="smSubmitText">Create Schedule</span></span>
                            <span class="sm-submit-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving…</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
    background: #1657c1; border: none;
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

/* ── Modal shell ── */
.sm-header { display:flex; align-items:flex-start; justify-content:space-between; background:#1657c1; padding:18px 22px; }
.sm-eyebrow { display:inline-block; font-size:.64rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:5px; }
.sm-title { font-size:1rem; font-weight:600; color:#fff; margin:0; }
.sm-section { padding:18px 22px; border-bottom:1px solid #f1f1ef; }
.sm-section:last-child { border-bottom:none; }
.sm-section-top { background:#fafaf9; }
.sm-section-personnel { background:#fff; }
.sm-label { font-size:.7rem; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#787774; display:block; margin-bottom:7px; }
.sm-hint { font-size:.75rem; color:#b0b0a8; font-weight:400; }
/* ── Type toggle ── */
.type-toggle { display:flex; gap:8px; }
.type-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:7px; padding:9px 16px; border-radius:8px; border:2px solid #e2e8f0; background:#fff; color:#64748b; font-size:.85rem; font-weight:500; cursor:pointer; transition:all .15s; }
.type-btn:hover { border-color:#94a3b8; color:#334155; background:#f8fafc; }
.type-btn.active-midwife { border-color:#16a34a; background:#f0fdf4; color:#15803d; }
.type-btn.active-doctor  { border-color:#1657c1; background:#eff6ff; color:#1657c1; }
/* ── Personnel cards ── */
.personnel-scroll { display:flex; gap:10px; overflow-x:auto; padding-bottom:6px; scrollbar-width:thin; scrollbar-color:#e2e8f0 transparent; }
.personnel-scroll::-webkit-scrollbar { height:4px; }
.personnel-scroll::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:4px; }
.personnel-placeholder { display:flex; align-items:center; gap:10px; color:#b0b0a8; font-size:.82rem; padding:12px 6px; }
.personnel-placeholder i { font-size:1.4rem; }
.p-card { display:flex; flex-direction:column; align-items:center; gap:6px; min-width:76px; padding:10px 8px; border-radius:10px; border:2px solid #e9e9e7; background:#fff; cursor:pointer; transition:all .15s; flex-shrink:0; text-align:center; }
.p-card:hover { border-color:#94a3b8; background:#f8fafc; }
.p-card.selected { border-color:#1657c1; background:#eff6ff; }
.p-avatar { width:40px; height:40px; border-radius:50%; background:#e2e8f0; color:#475569; display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:700; flex-shrink:0; text-transform:uppercase; letter-spacing:.5px; }
.p-card.selected .p-avatar { background:#1657c1; color:#fff; }
.p-card-name { font-size:.68rem; font-weight:600; color:#374151; line-height:1.2; word-break:break-word; max-width:66px; }
.p-card.selected .p-card-name { color:#1657c1; }
.p-prefill-dot { width:6px; height:6px; border-radius:50%; background:#10b981; flex-shrink:0; display:none; }
.p-card.has-prior .p-prefill-dot { display:block; }
/* ── Week nav + grid header ── */
.sm-grid-header { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
.week-nav { display:flex; align-items:center; gap:6px; }
.wk-btn { width:28px; height:28px; border-radius:6px; border:1px solid #e2e8f0; background:#fff; color:#374151; display:flex; align-items:center; justify-content:center; font-size:.75rem; cursor:pointer; transition:all .1s; }
.wk-btn:hover { background:#f1f5f9; border-color:#94a3b8; }
.wk-label { font-size:.82rem; font-weight:600; color:#1e293b; white-space:nowrap; min-width:180px; text-align:center; }
.quick-actions { display:flex; gap:6px; flex-wrap:wrap; margin-left:auto; }
.qa-btn { font-size:.72rem; font-weight:500; padding:4px 10px; border-radius:20px; border:1px solid #e2e8f0; background:#fff; color:#374151; cursor:pointer; transition:all .12s; white-space:nowrap; }
.qa-btn:hover { background:#f1f5f9; border-color:#94a3b8; }
.qa-btn-danger { border-color:#fecaca; color:#dc2626; }
.qa-btn-danger:hover { background:#fef2f2; border-color:#fca5a5; }
/* ── Shift grid ── */
.shift-grid-wrap { overflow-x:auto; border-radius:10px; border:1px solid #e9e9e7; }
.shift-grid { width:100%; border-collapse:collapse; table-layout:fixed; }
.shift-grid thead th { padding:8px 4px; text-align:center; font-size:.7rem; font-weight:700; letter-spacing:.3px; text-transform:uppercase; color:#6b7280; background:#f8fafc; border-bottom:1px solid #e9e9e7; }
.shift-grid thead th:first-child { text-align:left; padding-left:12px; width:120px; }
.shift-grid tbody td { padding:5px 4px; border-bottom:1px solid #f3f4f6; vertical-align:top; }
.shift-grid tbody tr:last-child td { border-bottom:none; }
.shift-grid tbody td:first-child { padding-left:12px; padding-right:8px; }
.sg-row-header { min-width:110px; }
.sg-shift-name { font-size:.75rem; font-weight:700; color:#374151; }
.morning-label   { color:#b45309; }
.afternoon-label { color:#1d4ed8; }
.sg-shift-time { font-size:.65rem; color:#9ca3af; margin-top:1px; }
.sg-weekend { background:#fafaf9; }
.sg-cell { width:100%; height:46px; border-radius:8px; border:2px dashed #e2e8f0; background:#fafafa; color:transparent; cursor:pointer; transition:all .12s; display:flex; align-items:center; justify-content:center; position:relative; }
.sg-cell:hover { border-color:#94a3b8; background:#f1f5f9; }
.sg-check { font-size:1rem; transition:opacity .12s; opacity:0; }
.sg-cell.active-morning  { border:2px solid #f59e0b; background:#fffbeb; color:#b45309; }
.sg-cell.active-morning .sg-check  { opacity:1; color:#d97706; }
.sg-cell.active-afternoon { border:2px solid #3b82f6; background:#eff6ff; color:#1d4ed8; }
.sg-cell.active-afternoon .sg-check { opacity:1; color:#2563eb; }
/* ── Custom slots ── */
.sg-custom-cell { min-width:90px; }
.sg-add-custom { width:100%; height:32px; border-radius:6px; border:2px dashed #d1d5db; background:transparent; color:#9ca3af; cursor:pointer; transition:all .12s; display:flex; align-items:center; justify-content:center; font-size:.85rem; }
.sg-add-custom:hover { border-color:#6b7280; color:#374151; background:#f9fafb; }
.custom-slots-list { display:flex; flex-direction:column; gap:4px; margin-top:4px; }
.custom-slot-group { display:flex; flex-direction:column; gap:2px; position:relative; }
.custom-slot-group input[type="time"] { width:100%; font-size:.68rem; padding:3px 5px; border:1px solid #e2e8f0; border-radius:5px; color:#374151; background:#fff; text-align:center; }
.custom-slot-group input[type="time"]:focus { outline:none; border-color:#1657c1; }
.cs-input-error { border-color:#ef4444 !important; background:#fff5f5 !important; }
.cs-error { display:none; align-items:center; gap:5px; font-size:.65rem; color:#b91c1c; font-weight:500; background:#fee2e2; border:1px solid #fca5a5; border-radius:4px; padding:4px 7px; margin-top:2px; line-height:1.3; }
.cs-error i { font-size:.7rem; flex-shrink:0; }
.custom-slot-sep { font-size:.6rem; color:#9ca3af; text-align:center; line-height:1; }
.custom-slot-remove { position:absolute; top:-5px; right:-5px; width:16px; height:16px; border-radius:50%; border:none; background:#ef4444; color:#fff; font-size:.6rem; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1; }
/* ── Legend ── */
.sg-legend { display:flex; align-items:center; gap:14px; margin-top:12px; flex-wrap:wrap; }
.sg-legend-item { display:flex; align-items:center; gap:5px; font-size:.72rem; color:#6b7280; }
.sg-legend-dot { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
.morning-dot   { background:#f59e0b; }
.afternoon-dot { background:#3b82f6; }
#smValidationMsg { padding:4px 0; }
.sm-submit-loading { display:flex; align-items:center; }

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
        const click=`showDetail('${esc(item.id)}','${item.type}')`;
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
    const actionLink=document.getElementById('eventModalActionLink');
    const actionLabel=document.getElementById('eventModalActionLabel');
    if(type==='event'){
        actionLink.href=`/events/${item.id}`;
        actionLabel.textContent='View Event';
        actionLink.classList.remove('d-none');
    } else if(type==='schedule'){
        actionLink.href='{{ route("rhu.schedules.index") }}';
        actionLabel.textContent='Edit Schedule';
        actionLink.classList.remove('d-none');
    } else {
        actionLink.classList.add('d-none');
    }
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

/* ── Schedule modal JS (mirrors schedules/index) ── */
const calAvailableMidwives = @json($availableMidwives ?? []);
const calAssignedDoctors   = @json($assignedDoctors ?? []);
const calMidwifeScheds     = @json($midwifeSchedules ?? []);
const calDoctorScheds      = @json($doctorSchedules ?? []);

function calBuildLookup(schedules){
    const map={};
    schedules.forEach(s=>{
        const pid=s.personnel_id; if(!pid)return;
        if(!map[pid]||(s.week_start||'')>(map[pid].week_start||''))map[pid]=s;
    });
    return map;
}
const calMidwifeLookup = calBuildLookup(calMidwifeScheds);
const calDoctorLookup  = calBuildLookup(calDoctorScheds);

const CAL_SHIFTS = {
    morning:   { label:'8:00 AM-12:00 PM', start:'08:00', end:'12:00' },
    afternoon: { label:'1:00 PM-5:00 PM',  start:'13:00', end:'17:00' },
};
const CAL_DAYS     = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
const CAL_WEEKDAYS = ['monday','tuesday','wednesday','thursday','friday'];

let calType='', calPersonnelId='', calPersonnelName='', calWeekStart=calGetThisMonday();

function calGetThisMonday(){
    const d=new Date(), day=d.getDay(), diff=day===0?-6:1-day;
    d.setDate(d.getDate()+diff); d.setHours(0,0,0,0); return d;
}
function calFmtDate(d){ return d.toISOString().split('T')[0]; }
function calFmtWeekLabel(mon){
    const sun=new Date(mon); sun.setDate(mon.getDate()+6);
    const o={month:'short',day:'numeric'};
    return mon.toLocaleDateString('en-US',o)+' – '+sun.toLocaleDateString('en-US',{...o,year:'numeric'});
}

function openAddModal(){
    calType=''; calPersonnelId=''; calPersonnelName=''; calWeekStart=calGetThisMonday();
    document.getElementById('smEyebrow').textContent='New Schedule';
    document.getElementById('smTitle').textContent='Add Weekly Schedule';
    document.getElementById('smSubmitText').textContent='Create Schedule';
    document.getElementById('smMethodField').innerHTML='';
    document.getElementById('schedForm').action='{{ route("rhu.schedules.store") }}';
    calResetTypeButtons(); calResetPersonnelArea(); calUpdateWeekLabel(); calClearGrid(); calClearValidation();
}

function selectType(type){
    calType=type;
    calResetTypeButtons();
    const btn=document.getElementById(type==='midwife'?'typeBtnMidwife':'typeBtnDoctor');
    btn.classList.add(type==='midwife'?'active-midwife':'active-doctor');
    calPersonnelId=''; calPersonnelName=''; calClearGrid();
    calLoadPersonnelCards(type,''); calClearValidation();
}

function calResetTypeButtons(){
    document.getElementById('typeBtnMidwife').className='type-btn';
    document.getElementById('typeBtnDoctor').className='type-btn';
}

function calLoadPersonnelCards(type, selectedId){
    const list=type==='midwife'?calAvailableMidwives:calAssignedDoctors;
    const lookup=type==='midwife'?calMidwifeLookup:calDoctorLookup;
    const scroll=document.getElementById('personnelScroll');
    const hint=document.getElementById('smPersonnelHint');
    if(!list.length){
        scroll.innerHTML=`<div class="personnel-placeholder"><i class="bi bi-person-x"></i><span>No ${type}s found</span></div>`;
        hint.textContent=`No ${type}s available`; return;
    }
    hint.textContent=`${list.length} available`;
    scroll.innerHTML='';
    list.forEach(p=>{
        const name=p.name||p.full_name||'Unknown';
        const initials=name.split(' ').map(w=>w[0]).join('').slice(0,2);
        const hasPrior=!!lookup[p.id], isSelected=p.id===selectedId;
        const card=document.createElement('div');
        card.className='p-card'+(hasPrior?' has-prior':'')+(isSelected?' selected':'');
        card.dataset.id=p.id; card.dataset.name=name;
        card.innerHTML=`<div class="p-avatar">${initials}</div><div class="p-card-name">${name}</div><div class="p-prefill-dot" title="Has prior schedule"></div>`;
        card.onclick=()=>calSelectPersonnel(p.id,name,card,type);
        scroll.appendChild(card);
    });
    if(selectedId){
        const card=scroll.querySelector(`[data-id="${selectedId}"]`);
        if(card)card.scrollIntoView({behavior:'smooth',block:'nearest',inline:'center'});
    }
}

function calSelectPersonnel(id,name,cardEl,type){
    calPersonnelId=id; calPersonnelName=name;
    document.querySelectorAll('.p-card').forEach(c=>c.classList.remove('selected'));
    cardEl.classList.add('selected');
    const lookup=(type||calType)==='midwife'?calMidwifeLookup:calDoctorLookup;
    const prior=lookup[id];
    calClearGrid();
    if(prior&&prior.schedule&&Object.keys(prior.schedule).length)calLoadScheduleIntoGrid(prior.schedule);
    calClearValidation();
}

function shiftWeek(dir){
    calWeekStart=new Date(calWeekStart);
    calWeekStart.setDate(calWeekStart.getDate()+dir*7);
    calUpdateWeekLabel();
}
function calUpdateWeekLabel(){ document.getElementById('wkLabel').textContent=calFmtWeekLabel(calWeekStart); }

function toggleCell(btn){
    const shift=btn.dataset.shift, activeClass='active-'+shift;
    btn.classList.toggle(activeClass,!btn.classList.contains(activeClass));
    calClearValidation();
}
function calClearGrid(){
    document.querySelectorAll('.sg-cell').forEach(b=>b.classList.remove('active-morning','active-afternoon'));
    CAL_DAYS.forEach(day=>{ const l=document.getElementById('customSlots_'+day); if(l)l.innerHTML=''; });
}
function calSetCell(day,shift,active){
    const cell=document.querySelector(`.sg-cell[data-day="${day}"][data-shift="${shift}"]`);
    if(cell)cell.classList.toggle('active-'+shift,active);
}
function calLoadScheduleIntoGrid(schedule){
    if(!schedule)return;
    CAL_DAYS.forEach(day=>{
        const slots=schedule[day]; if(!slots||!Array.isArray(slots))return;
        slots.forEach(slot=>{
            if(!slot)return;
            const norm=slot.trim().replace(/\s+/g,'');
            if(norm==='8:00AM-12:00PM'||norm==='8:00AM–12:00PM')calSetCell(day,'morning',true);
            else if(norm==='1:00PM-5:00PM'||norm==='1:00PM–5:00PM')calSetCell(day,'afternoon',true);
            else{
                const parts=slot.split('-');
                if(parts.length>=2){
                    const s24=calConvert12To24(parts[0].trim());
                    const e24=calConvert12To24(parts.slice(1).join('-').trim());
                    if(s24||e24)addCustomSlot(day,s24,e24);
                }
            }
        });
    });
}

function applyPattern(pattern){
    if(pattern==='clear'){calClearGrid();return;}
    const days=pattern.includes('weekday')?CAL_WEEKDAYS:CAL_DAYS;
    if(pattern==='weekday-am')days.forEach(d=>calSetCell(d,'morning',true));
    else if(pattern==='weekday-pm')days.forEach(d=>calSetCell(d,'afternoon',true));
    else if(pattern==='full-week')days.forEach(d=>{calSetCell(d,'morning',true);calSetCell(d,'afternoon',true);});
    calClearValidation();
}

function addCustomSlot(day,start24,end24){
    start24=start24||''; end24=end24||'';
    const list=document.getElementById('customSlots_'+day); if(!list)return;
    const grp=document.createElement('div'); grp.className='custom-slot-group';
    grp.innerHTML=`
        <input type="time" class="cs-start" value="${start24}" placeholder="Start">
        <div class="custom-slot-sep">to</div>
        <input type="time" class="cs-end" value="${end24}" placeholder="End">
        <button type="button" class="custom-slot-remove" onclick="this.closest('.custom-slot-group').remove()" title="Remove">×</button>
        <div class="cs-error" style="display:none;"><i class="bi bi-exclamation-circle-fill"></i>End time must be after start time — shifts cannot cross midnight.</div>`;
    list.appendChild(grp);
    const si=grp.querySelector('.cs-start'), ei=grp.querySelector('.cs-end'), err=grp.querySelector('.cs-error');
    function validate(){ const s=si.value,e=ei.value; if(!s||!e){clearErr();return;} e<=s?showErr():clearErr(); }
    function showErr(){ err.style.display='flex'; si.classList.add('cs-input-error'); ei.classList.add('cs-input-error'); }
    function clearErr(){ err.style.display='none'; si.classList.remove('cs-input-error'); ei.classList.remove('cs-input-error'); }
    si.addEventListener('change',validate); ei.addEventListener('change',validate);
    if(start24&&end24)validate();
}

function calConvert12To24(t){
    if(!t)return'';
    const m=t.trim().toUpperCase().match(/(\d{1,2}):(\d{2})\s*(AM|PM)/);
    if(!m)return'';
    let h=parseInt(m[1]);
    if(m[3]==='PM'&&h!==12)h+=12; else if(m[3]==='AM'&&h===12)h=0;
    return`${String(h).padStart(2,'0')}:${m[2]}`;
}
function calFmt12(t24){
    if(!t24)return'';
    const [h,m]=t24.split(':').map(Number); if(isNaN(h))return t24;
    const p=h>=12?'PM':'AM', d=h===0?12:h>12?h-12:h;
    return`${d}:${String(m).padStart(2,'0')} ${p}`;
}

function showCalValidation(msg){ document.getElementById('smValidationMsg').classList.remove('d-none'); document.getElementById('smValidationText').textContent=msg; }
function calClearValidation(){ document.getElementById('smValidationMsg').classList.add('d-none'); }
function calResetPersonnelArea(){
    document.getElementById('personnelScroll').innerHTML=`<div class="personnel-placeholder"><i class="bi bi-people"></i><span>Select a schedule type to see available personnel</span></div>`;
    document.getElementById('smPersonnelHint').textContent='Select a schedule type first';
}

document.getElementById('schedForm').addEventListener('submit',function(e){
    e.preventDefault();
    const barangayId=document.getElementById('smLocation').value;
    if(!barangayId){showCalValidation('Please select a location.');return;}
    if(!calType){showCalValidation('Please select a schedule type.');return;}
    if(!calPersonnelId){showCalValidation('Please select a personnel.');return;}
    const invalidSlots=document.querySelectorAll('.custom-slot-group .cs-input-error');
    if(invalidSlots.length>0){showCalValidation('One or more custom shifts have an invalid time range.');invalidSlots[0].closest('.custom-slot-group').scrollIntoView({behavior:'smooth',block:'center'});return;}
    const scheduleData={};
    CAL_DAYS.forEach(day=>{
        const slots=[];
        ['morning','afternoon'].forEach(shift=>{
            const cell=document.querySelector(`.sg-cell[data-day="${day}"][data-shift="${shift}"]`);
            if(cell&&cell.classList.contains('active-'+shift))slots.push(CAL_SHIFTS[shift].label);
        });
        const list=document.getElementById('customSlots_'+day);
        if(list)list.querySelectorAll('.custom-slot-group').forEach(grp=>{
            const s=grp.querySelector('.cs-start')?.value, en=grp.querySelector('.cs-end')?.value;
            if(s&&en)slots.push(`${calFmt12(s)}-${calFmt12(en)}`);
        });
        if(slots.length)scheduleData[day]=slots;
    });
    if(Object.keys(scheduleData).length===0){showCalValidation('Please assign at least one shift.');return;}
    document.getElementById('smBarangayId').value=barangayId;
    document.getElementById('smType').value=calType;
    document.getElementById('smPersonnelId').value=calPersonnelId;
    document.getElementById('smPersonnelName').value=calPersonnelName;
    const sun=new Date(calWeekStart); sun.setDate(calWeekStart.getDate()+6);
    document.getElementById('smWeekStart').value=calFmtDate(calWeekStart);
    document.getElementById('smWeekEnd').value=calFmtDate(sun);
    this.querySelectorAll('.dyn-sched-input').forEach(el=>el.remove());
    Object.entries(scheduleData).forEach(([day,slots])=>{
        slots.forEach(slot=>{
            const inp=document.createElement('input');
            inp.type='hidden'; inp.name=`schedule[${day}][]`; inp.value=slot; inp.className='dyn-sched-input';
            this.appendChild(inp);
        });
    });
    document.querySelector('.sm-submit-label').classList.add('d-none');
    document.querySelector('.sm-submit-loading').classList.remove('d-none');
    document.getElementById('smSubmitBtn').disabled=true;
    this.submit();
});

/* ── Init ── */
document.addEventListener('DOMContentLoaded', ()=>{
    loadMonthData(currentMonth);
    calUpdateWeekLabel();
    document.querySelectorAll('.toast').forEach(t=>{
        setTimeout(()=>{ bootstrap.Toast.getOrCreateInstance(t).hide(); }, 4000);
    });
    document.getElementById('addScheduleModal').addEventListener('show.bs.modal', openAddModal);
});
</script>
@endsection
