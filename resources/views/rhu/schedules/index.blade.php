@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Flash Toasts --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert" id="successToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert" id="errorToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Schedules</h2>
            <p class="text-muted mb-0 small">Manage weekly staff schedules by location</p>
        </div>
        <button class="btn btn-add-sched" onclick="openAddModal()" data-bs-toggle="modal" data-bs-target="#schedModal">
            <i class="bi bi-plus-lg me-1"></i>Add Schedule
        </button>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-calendar2-week-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ count($midwifeSchedules) + count($doctorSchedules) }}</div>
                    <div class="stat-label">Total Schedules</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-heart-pulse-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ count($midwifeSchedules) }}</div>
                    <div class="stat-label">Midwife</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-person-vcard-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ count($doctorSchedules) }}</div>
                    <div class="stat-label">Doctor</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ count($barangayOptions) }}</div>
                    <div class="stat-label">Locations</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="sched-tabs mb-3">
        <button class="sched-tab active" id="tabMidwife" onclick="switchTab('midwife')">
            <i class="bi bi-heart-pulse me-1"></i>Midwife Schedules
            <span class="tab-count">{{ count($midwifeSchedules) }}</span>
        </button>
        <button class="sched-tab" id="tabDoctor" onclick="switchTab('doctor')">
            <i class="bi bi-person-vcard me-1"></i>Doctor Schedules
            <span class="tab-count">{{ count($doctorSchedules) }}</span>
        </button>
    </div>

    {{-- Midwife Schedules --}}
    <div id="paneMidwife" class="sched-pane">
        @if(count($midwifeSchedules) > 0)
            <div class="row g-3">
                @foreach($midwifeSchedules as $schedule)
                <div class="col-md-6 col-xl-4">
                    <div class="sched-card">
                        <div class="sched-card-header">
                            <div>
                                <div class="sched-badge">Midwife</div>
                                <div class="sched-name">{{ $schedule['personnel_name'] }}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="sched-action-btn" onclick="openEditModal('{{ $schedule['id'] }}','midwife','{{ addslashes($schedule['personnel_id'] ?? '') }}','{{ addslashes($schedule['personnel_name']) }}',{{ json_encode($schedule['schedule'] ?? []) }},'{{ $schedule['week_start'] ?? '' }}','{{ $schedule['week_end'] ?? '' }}','{{ $schedule['barangay_id'] ?? $selectedBarangayId }}')" data-bs-toggle="modal" data-bs-target="#schedModal" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="sched-action-btn danger" onclick="confirmDelete('{{ $schedule['id'] }}','{{ addslashes($schedule['personnel_name']) }}','{{ $schedule['barangay_id'] ?? $selectedBarangayId }}')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sched-card-body">
                            @if(isset($schedule['week_start']) && isset($schedule['week_end']))
                            <div class="sched-week-badge">
                                <i class="bi bi-calendar-range me-1"></i>
                                {{ \Carbon\Carbon::parse($schedule['week_start'])->format('M d') }} – {{ \Carbon\Carbon::parse($schedule['week_end'])->format('M d, Y') }}
                            </div>
                            @endif
                            <div class="day-rows">
                                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat','sunday'=>'Sun'] as $day => $abbr)
                                    @if(isset($schedule['schedule'][$day]) && !empty($schedule['schedule'][$day]))
                                    <div class="day-row">
                                        <span class="day-abbr {{ in_array($day, ['saturday','sunday']) ? 'weekend' : '' }}">{{ $abbr }}</span>
                                        <div class="time-chips">
                                            @foreach($schedule['schedule'][$day] as $slot)
                                                @if(is_string($slot) && $slot)
                                                <span class="time-chip">{{ $slot }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="sched-empty">
                <i class="bi bi-calendar-x"></i>
                <div class="sched-empty-title">No Midwife Schedules</div>
                <div class="sched-empty-text">Create a midwife schedule using the Add Schedule button above.</div>
            </div>
        @endif
    </div>

    {{-- Doctor Schedules --}}
    <div id="paneDoctor" class="sched-pane d-none">
        @if(count($doctorSchedules) > 0)
            <div class="row g-3">
                @foreach($doctorSchedules as $schedule)
                <div class="col-md-6 col-xl-4">
                    <div class="sched-card">
                        <div class="sched-card-header">
                            <div>
                                <div class="sched-badge doctor">Doctor</div>
                                <div class="sched-name">{{ $schedule['personnel_name'] }}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="sched-action-btn" onclick="openEditModal('{{ $schedule['id'] }}','doctor','{{ addslashes($schedule['personnel_id'] ?? '') }}','{{ addslashes($schedule['personnel_name']) }}',{{ json_encode($schedule['schedule'] ?? []) }},'{{ $schedule['week_start'] ?? '' }}','{{ $schedule['week_end'] ?? '' }}','{{ $schedule['barangay_id'] ?? $selectedBarangayId }}')" data-bs-toggle="modal" data-bs-target="#schedModal" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="sched-action-btn danger" onclick="confirmDelete('{{ $schedule['id'] }}','{{ addslashes($schedule['personnel_name']) }}','{{ $schedule['barangay_id'] ?? $selectedBarangayId }}')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sched-card-body">
                            @if(isset($schedule['week_start']) && isset($schedule['week_end']))
                            <div class="sched-week-badge">
                                <i class="bi bi-calendar-range me-1"></i>
                                {{ \Carbon\Carbon::parse($schedule['week_start'])->format('M d') }} – {{ \Carbon\Carbon::parse($schedule['week_end'])->format('M d, Y') }}
                            </div>
                            @endif
                            <div class="day-rows">
                                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat','sunday'=>'Sun'] as $day => $abbr)
                                    @if(isset($schedule['schedule'][$day]) && !empty($schedule['schedule'][$day]))
                                    <div class="day-row">
                                        <span class="day-abbr {{ in_array($day, ['saturday','sunday']) ? 'weekend' : '' }}">{{ $abbr }}</span>
                                        <div class="time-chips">
                                            @foreach($schedule['schedule'][$day] as $slot)
                                                @if(is_string($slot) && $slot)
                                                <span class="time-chip">{{ $slot }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="sched-empty">
                <i class="bi bi-calendar-x"></i>
                <div class="sched-empty-title">No Doctor Schedules</div>
                <div class="sched-empty-text">Create a doctor schedule using the Add Schedule button above.</div>
            </div>
        @endif
    </div>
</div>

{{-- ─────────────────────────────────────────────
     Unified Schedule Modal (Add + Edit)
───────────────────────────────────────────── --}}
<div class="modal fade" id="schedModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">

            {{-- Modal header --}}
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
                {{-- schedule[day][] inputs injected on submit --}}

                <div class="modal-body p-0">

                    {{-- ── Section 1: Location + Type ── --}}
                    <div class="sm-section sm-section-top">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="sm-label">Location</label>
                                <select class="form-select form-select-sm rounded-3" id="smLocation">
                                    <option value="">— Select location —</option>
                                    @foreach($barangayOptions as $opt)
                                        <option value="{{ $opt['id'] }}" {{ $opt['id'] === $selectedBarangayId ? 'selected' : '' }}>{{ $opt['name'] }}</option>
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
                                    {{-- Morning row --}}
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
                                    {{-- Afternoon row --}}
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
                                    {{-- Custom row --}}
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

                        {{-- Legend --}}
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

{{-- ─── Delete Confirm Modal ─── --}}
<div class="modal fade" id="deleteSchedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="sm-header" style="background:#b91c1c;">
                <div>
                    <div class="sm-eyebrow" style="background:rgba(255,255,255,.15);">Confirm Delete</div>
                    <h5 class="sm-title">Delete Schedule</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-1">Are you sure you want to delete the schedule for <strong id="deleteSchedName"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteSchedForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="barangay_id" id="deleteSchedBarangay">
                    <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-trash me-1"></i>Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
*, *::before, *::after { box-sizing: border-box; }

/* ── Page chrome ── */
.btn-add-sched {
    background:#1657c1; border:none; color:#fff;
    font-size:.82rem; font-weight:500; padding:6px 16px;
    border-radius:6px; transition:opacity .1s;
}
.btn-add-sched:hover { opacity:.82; color:#fff; }

.stat-card { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:6px; background:#fff; border:1px solid #e9e9e7; }
.stat-icon { width:36px; height:36px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#f1f1ef; color:#787774; }
.stat-value { font-size:1.5rem; font-weight:700; color:#37352f; line-height:1; }
.stat-label { font-size:.72rem; color:#9b9b9b; font-weight:400; margin-top:2px; }

.sched-tabs { display:flex; gap:4px; border-bottom:1px solid #e9e9e7; }
.sched-tab { background:none; border:none; padding:8px 16px 10px; font-size:.85rem; font-weight:500; color:#787774; border-bottom:2px solid transparent; margin-bottom:-1px; cursor:pointer; transition:all .15s; border-radius:4px 4px 0 0; display:flex; align-items:center; gap:6px; }
.sched-tab:hover { color:#37352f; background:#f7f7f5; }
.sched-tab.active { color:#37352f; border-bottom-color:#37352f; }
.tab-count { background:#f1f1ef; color:#787774; font-size:.68rem; font-weight:600; padding:1px 7px; border-radius:10px; }
.sched-tab.active .tab-count { background:#37352f; color:#fff; }

/* ── Schedule cards ── */
.sched-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; transition:box-shadow .15s; }
.sched-card:hover { box-shadow:0 2px 12px rgba(0,0,0,.08); }
.sched-card-header { display:flex; align-items:flex-start; justify-content:space-between; padding:14px 16px 10px; background:#1657c1; }
.sched-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; margin-bottom:4px; }
.sched-badge.doctor { background:rgba(99,179,237,.25); color:#bfdbfe; }
.sched-name { font-size:.9rem; font-weight:600; color:#fff; }
.sched-action-btn { width:26px; height:26px; border-radius:4px; border:none; background:rgba(255,255,255,.12); color:rgba(255,255,255,.8); display:flex; align-items:center; justify-content:center; font-size:.75rem; cursor:pointer; transition:background .1s; }
.sched-action-btn:hover { background:rgba(255,255,255,.22); color:#fff; }
.sched-action-btn.danger:hover { background:rgba(239,68,68,.35); color:#fecaca; }
.sched-card-body { padding:12px 16px 14px; }
.sched-week-badge { display:inline-flex; align-items:center; font-size:.73rem; font-weight:500; color:#787774; background:#f1f1ef; padding:3px 10px; border-radius:4px; margin-bottom:10px; }
.day-rows { display:flex; flex-direction:column; gap:4px; }
.day-row { display:flex; align-items:center; gap:8px; }
.day-abbr { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.3px; color:#787774; background:#f1f1ef; width:28px; height:22px; border-radius:3px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.day-abbr.weekend { color:#9b9b9b; }
.time-chips { display:flex; flex-wrap:wrap; gap:3px; }
.time-chip { background:#fef9c3; color:#854d0e; font-size:.69rem; font-weight:500; padding:2px 8px; border-radius:3px; }
.sched-empty { text-align:center; padding:60px 20px; color:#9b9b9b; background:#fff; border:1px solid #e9e9e7; border-radius:8px; }
.sched-empty i { font-size:2.5rem; margin-bottom:12px; display:block; }
.sched-empty-title { font-size:1rem; font-weight:600; color:#37352f; margin-bottom:4px; }
.sched-empty-text { font-size:.82rem; }

/* ── Modal shell ── */
.sm-header { display:flex; align-items:flex-start; justify-content:space-between; background:#1657c1; padding:18px 22px; }
.sm-eyebrow { display:inline-block; font-size:.64rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:5px; }
.sm-title { font-size:1rem; font-weight:600; color:#fff; margin:0; }

/* ── Modal sections ── */
.sm-section { padding:18px 22px; border-bottom:1px solid #f1f1ef; }
.sm-section:last-child { border-bottom:none; }
.sm-section-top { background:#fafaf9; }
.sm-section-personnel { background:#fff; }
.sm-label { font-size:.7rem; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#787774; display:block; margin-bottom:7px; }
.sm-hint { font-size:.75rem; color:#b0b0a8; font-weight:400; }

/* ── Type toggle ── */
.type-toggle { display:flex; gap:8px; }
.type-btn {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:9px 16px; border-radius:8px; border:2px solid #e2e8f0;
    background:#fff; color:#64748b; font-size:.85rem; font-weight:500;
    cursor:pointer; transition:all .15s;
}
.type-btn:hover { border-color:#94a3b8; color:#334155; background:#f8fafc; }
.type-btn.active-midwife { border-color:#16a34a; background:#f0fdf4; color:#15803d; }
.type-btn.active-doctor  { border-color:#1657c1; background:#eff6ff; color:#1657c1; }

/* ── Personnel cards ── */
.personnel-scroll {
    display:flex; gap:10px; overflow-x:auto; padding-bottom:6px;
    scrollbar-width:thin; scrollbar-color:#e2e8f0 transparent;
}
.personnel-scroll::-webkit-scrollbar { height:4px; }
.personnel-scroll::-webkit-scrollbar-track { background:transparent; }
.personnel-scroll::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:4px; }
.personnel-placeholder { display:flex; align-items:center; gap:10px; color:#b0b0a8; font-size:.82rem; padding:12px 6px; }
.personnel-placeholder i { font-size:1.4rem; }
.p-card {
    display:flex; flex-direction:column; align-items:center; gap:6px;
    min-width:76px; padding:10px 8px; border-radius:10px;
    border:2px solid #e9e9e7; background:#fff; cursor:pointer;
    transition:all .15s; flex-shrink:0; text-align:center;
}
.p-card:hover { border-color:#94a3b8; background:#f8fafc; }
.p-card.selected { border-color:#1657c1; background:#eff6ff; }
.p-avatar {
    width:40px; height:40px; border-radius:50%;
    background:#e2e8f0; color:#475569;
    display:flex; align-items:center; justify-content:center;
    font-size:.85rem; font-weight:700; flex-shrink:0;
    text-transform:uppercase; letter-spacing:.5px;
}
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
.shift-grid thead th {
    padding:8px 4px; text-align:center; font-size:.7rem; font-weight:700;
    letter-spacing:.3px; text-transform:uppercase; color:#6b7280;
    background:#f8fafc; border-bottom:1px solid #e9e9e7;
}
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

/* ── Shift cell toggle ── */
.sg-cell {
    width:100%; height:46px; border-radius:8px;
    border:2px dashed #e2e8f0; background:#fafafa;
    color:transparent; cursor:pointer; transition:all .12s;
    display:flex; align-items:center; justify-content:center;
    position:relative;
}
.sg-cell:hover { border-color:#94a3b8; background:#f1f5f9; }
.sg-check { font-size:1rem; transition:opacity .12s; opacity:0; }

.sg-cell.active-morning {
    border:2px solid #f59e0b; background:#fffbeb;
    color:#b45309;
}
.sg-cell.active-morning .sg-check { opacity:1; color:#d97706; }

.sg-cell.active-afternoon {
    border:2px solid #3b82f6; background:#eff6ff;
    color:#1d4ed8;
}
.sg-cell.active-afternoon .sg-check { opacity:1; color:#2563eb; }

/* ── Custom slots ── */
.sg-custom-cell { min-width:90px; }
.sg-add-custom {
    width:100%; height:32px; border-radius:6px;
    border:2px dashed #d1d5db; background:transparent;
    color:#9ca3af; cursor:pointer; transition:all .12s;
    display:flex; align-items:center; justify-content:center;
    font-size:.85rem;
}
.sg-add-custom:hover { border-color:#6b7280; color:#374151; background:#f9fafb; }
.custom-slots-list { display:flex; flex-direction:column; gap:4px; margin-top:4px; }
.custom-slot-group { display:flex; flex-direction:column; gap:2px; position:relative; }
.custom-slot-group input[type="time"] {
    width:100%; font-size:.68rem; padding:3px 5px;
    border:1px solid #e2e8f0; border-radius:5px; color:#374151;
    background:#fff; text-align:center;
}
.custom-slot-group input[type="time"]:focus { outline:none; border-color:#1657c1; }
.custom-slot-sep { font-size:.6rem; color:#9ca3af; text-align:center; line-height:1; }
.custom-slot-remove {
    position:absolute; top:-5px; right:-5px; width:16px; height:16px;
    border-radius:50%; border:none; background:#ef4444; color:#fff;
    font-size:.6rem; cursor:pointer; display:flex; align-items:center; justify-content:center;
    line-height:1;
}

/* ── Legend ── */
.sg-legend { display:flex; align-items:center; gap:14px; margin-top:12px; flex-wrap:wrap; }
.sg-legend-item { display:flex; align-items:center; gap:5px; font-size:.72rem; color:#6b7280; }
.sg-legend-dot { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
.morning-dot   { background:#f59e0b; }
.afternoon-dot { background:#3b82f6; }

/* ── Validation ── */
#smValidationMsg { padding:4px 0; }

/* ── Submit button loading ── */
.sm-submit-loading { display:flex; align-items:center; }
</style>

<script>
// ── Data from PHP ────────────────────────────────────
const availableMidwives = @json($availableMidwives);
const assignedDoctors   = @json($assignedDoctors);
const allMidwifeScheds  = @json($midwifeSchedules);
const allDoctorScheds   = @json($doctorSchedules);

// Build prior-schedule lookup: personnel_id → most recent schedule
function buildLookup(schedules) {
    const map = {};
    schedules.forEach(s => {
        const pid = s.personnel_id;
        if (!pid) return;
        if (!map[pid] || (s.week_start || '') > (map[pid].week_start || '')) map[pid] = s;
    });
    return map;
}
const midwifeLookup = buildLookup(allMidwifeScheds);
const doctorLookup  = buildLookup(allDoctorScheds);

// Shift definitions
const SHIFTS = {
    morning:   { label: '8:00 AM-12:00 PM', start: '08:00', end: '12:00' },
    afternoon: { label: '1:00 PM-5:00 PM',  start: '13:00', end: '17:00' },
};
const DAYS = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
const WEEKDAYS = ['monday','tuesday','wednesday','thursday','friday'];

// ── State ───────────────────────────────────────────
let smMode       = 'add';   // 'add' | 'edit'
let smEditId     = null;
let smType       = null;
let smPersonnelId   = '';
let smPersonnelName = '';
let currentWeekStart = getThisMonday(); // Date object

function getThisMonday() {
    const d = new Date();
    const day = d.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    d.setHours(0,0,0,0);
    return d;
}

function fmtDate(d) {
    return d.toISOString().split('T')[0];
}

function fmtWeekLabel(monDate) {
    const sun = new Date(monDate);
    sun.setDate(monDate.getDate() + 6);
    const opts = { month: 'short', day: 'numeric' };
    return monDate.toLocaleDateString('en-US', opts) + ' – ' + sun.toLocaleDateString('en-US', { ...opts, year: 'numeric' });
}

// ── Tab switching ────────────────────────────────────
function switchTab(tab) {
    document.getElementById('paneMidwife').classList.toggle('d-none', tab !== 'midwife');
    document.getElementById('paneDoctor').classList.toggle('d-none', tab !== 'doctor');
    document.getElementById('tabMidwife').classList.toggle('active', tab === 'midwife');
    document.getElementById('tabDoctor').classList.toggle('active', tab === 'doctor');
}

// ── Open Add Modal ───────────────────────────────────
function openAddModal() {
    smMode = 'add';
    smEditId = null;
    smType = null;
    smPersonnelId = '';
    smPersonnelName = '';
    currentWeekStart = getThisMonday();

    document.getElementById('smEyebrow').textContent = 'New Schedule';
    document.getElementById('smTitle').textContent   = 'Add Weekly Schedule';
    document.getElementById('smSubmitText').textContent = 'Create Schedule';
    document.getElementById('smMethodField').innerHTML = '';
    document.getElementById('schedForm').action = '{{ route("rhu.schedules.store") }}';

    resetTypeButtons();
    resetPersonnelArea();
    updateWeekLabel();
    clearGrid();
    clearValidation();
}

// ── Open Edit Modal ──────────────────────────────────
function openEditModal(id, type, personnelId, personnelName, schedule, weekStart, weekEnd, barangayId) {
    smMode = 'edit';
    smEditId = id;
    smType = type;
    smPersonnelId   = personnelId;
    smPersonnelName = personnelName;

    // Set week from existing data
    if (weekStart) {
        const d = new Date(weekStart + 'T00:00:00');
        // normalise to Monday
        const day = d.getDay();
        const diff = day === 0 ? -6 : 1 - day;
        d.setDate(d.getDate() + diff);
        currentWeekStart = d;
    }

    document.getElementById('smEyebrow').textContent = 'Edit Schedule';
    document.getElementById('smTitle').textContent   = personnelName;
    document.getElementById('smSubmitText').textContent = 'Update Schedule';
    document.getElementById('smMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('schedForm').action = `/rhu/schedules/${id}`;
    document.getElementById('smLocation').value = barangayId;

    // Set type buttons
    resetTypeButtons();
    if (type) {
        const btn = document.getElementById(type === 'midwife' ? 'typeBtnMidwife' : 'typeBtnDoctor');
        if (btn) btn.classList.add(type === 'midwife' ? 'active-midwife' : 'active-doctor');
        loadPersonnelCards(type, personnelId);
    }

    updateWeekLabel();
    clearGrid();
    loadScheduleIntoGrid(schedule);
    clearValidation();
}

// ── Type selection ───────────────────────────────────
function selectType(type) {
    smType = type;
    resetTypeButtons();
    const btn = document.getElementById(type === 'midwife' ? 'typeBtnMidwife' : 'typeBtnDoctor');
    btn.classList.add(type === 'midwife' ? 'active-midwife' : 'active-doctor');

    // Reset personnel if type changes in add mode
    if (smMode === 'add') {
        smPersonnelId   = '';
        smPersonnelName = '';
        clearGrid();
    }

    loadPersonnelCards(type, smPersonnelId);
    clearValidation();
}

function resetTypeButtons() {
    document.getElementById('typeBtnMidwife').className = 'type-btn';
    document.getElementById('typeBtnDoctor').className  = 'type-btn';
}

function loadPersonnelCards(type, selectedId) {
    const list = type === 'midwife' ? availableMidwives : assignedDoctors;
    const lookup = type === 'midwife' ? midwifeLookup : doctorLookup;
    const scroll = document.getElementById('personnelScroll');
    const hint   = document.getElementById('smPersonnelHint');

    if (!list.length) {
        scroll.innerHTML = `<div class="personnel-placeholder"><i class="bi bi-person-x"></i><span>No ${type}s found</span></div>`;
        hint.textContent = `No ${type}s available`;
        return;
    }

    hint.textContent = `${list.length} available`;
    scroll.innerHTML = '';

    list.forEach(p => {
        const name = p.name || p.full_name || 'Unknown';
        const initials = name.split(' ').map(w => w[0]).join('').slice(0, 2);
        const hasPrior = !!lookup[p.id];
        const isSelected = p.id === selectedId;

        const card = document.createElement('div');
        card.className = 'p-card' + (hasPrior ? ' has-prior' : '') + (isSelected ? ' selected' : '');
        card.dataset.id   = p.id;
        card.dataset.name = name;
        card.innerHTML = `
            <div class="p-avatar">${initials}</div>
            <div class="p-card-name">${name}</div>
            <div class="p-prefill-dot" title="Has prior schedule"></div>`;
        card.onclick = () => selectPersonnel(p.id, name, card, type);
        scroll.appendChild(card);
    });

    // Auto-select if selectedId given (edit mode)
    if (selectedId) {
        const card = scroll.querySelector(`[data-id="${selectedId}"]`);
        if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
}

function selectPersonnel(id, name, cardEl, type) {
    smPersonnelId   = id;
    smPersonnelName = name;

    // Highlight card
    document.querySelectorAll('.p-card').forEach(c => c.classList.remove('selected'));
    cardEl.classList.add('selected');

    // Pre-fill from prior schedule if in add mode
    if (smMode === 'add') {
        const lookup = (type || smType) === 'midwife' ? midwifeLookup : doctorLookup;
        const prior  = lookup[id];
        if (prior && prior.schedule && Object.keys(prior.schedule).length) {
            clearGrid();
            loadScheduleIntoGrid(prior.schedule);
        } else {
            clearGrid();
        }
    }

    clearValidation();
}

// ── Week navigation ──────────────────────────────────
function shiftWeek(dir) {
    currentWeekStart = new Date(currentWeekStart);
    currentWeekStart.setDate(currentWeekStart.getDate() + dir * 7);
    updateWeekLabel();
}

function updateWeekLabel() {
    document.getElementById('wkLabel').textContent = fmtWeekLabel(currentWeekStart);
}

// ── Grid management ──────────────────────────────────
function toggleCell(btn) {
    const shift = btn.dataset.shift; // 'morning' | 'afternoon'
    const activeClass = 'active-' + shift;
    const isActive = btn.classList.contains(activeClass);
    btn.classList.toggle(activeClass, !isActive);
    clearValidation();
}

function clearGrid() {
    document.querySelectorAll('.sg-cell').forEach(btn => {
        btn.classList.remove('active-morning', 'active-afternoon');
    });
    DAYS.forEach(day => {
        const list = document.getElementById('customSlots_' + day);
        if (list) list.innerHTML = '';
    });
}

function loadScheduleIntoGrid(schedule) {
    if (!schedule) return;
    DAYS.forEach(day => {
        const slots = schedule[day];
        if (!slots || !Array.isArray(slots)) return;
        slots.forEach(slot => {
            if (!slot) return;
            const norm = slot.trim().replace(/\s+/g, '');
            if (norm === '8:00AM-12:00PM' || norm === '8:00AM–12:00PM') {
                setCell(day, 'morning', true);
            } else if (norm === '1:00PM-5:00PM' || norm === '1:00PM–5:00PM') {
                setCell(day, 'afternoon', true);
            } else {
                // Custom slot — parse 12h to 24h and add
                const parts = slot.split('-');
                if (parts.length >= 2) {
                    const start24 = convert12To24(parts[0].trim());
                    const end24   = convert12To24(parts.slice(1).join('-').trim());
                    if (start24 || end24) addCustomSlotWithValues(day, start24, end24);
                }
            }
        });
    });
}

function setCell(day, shift, active) {
    const cell = document.querySelector(`.sg-cell[data-day="${day}"][data-shift="${shift}"]`);
    if (cell) cell.classList.toggle('active-' + shift, active);
}

// ── Quick patterns ───────────────────────────────────
function applyPattern(pattern) {
    if (pattern === 'clear') { clearGrid(); return; }
    const days = pattern.includes('weekday') ? WEEKDAYS : DAYS;
    if (pattern === 'weekday-am') {
        days.forEach(d => setCell(d, 'morning', true));
    } else if (pattern === 'weekday-pm') {
        days.forEach(d => setCell(d, 'afternoon', true));
    } else if (pattern === 'full-week') {
        days.forEach(d => { setCell(d, 'morning', true); setCell(d, 'afternoon', true); });
    }
    clearValidation();
}

// ── Custom slots ─────────────────────────────────────
function addCustomSlot(day) {
    addCustomSlotWithValues(day, '', '');
}

function addCustomSlotWithValues(day, start24, end24) {
    const list = document.getElementById('customSlots_' + day);
    if (!list) return;
    const grp = document.createElement('div');
    grp.className = 'custom-slot-group';
    grp.innerHTML = `
        <input type="time" class="cs-start" value="${start24}" placeholder="Start">
        <div class="custom-slot-sep">to</div>
        <input type="time" class="cs-end"   value="${end24}"   placeholder="End">
        <button type="button" class="custom-slot-remove" onclick="this.closest('.custom-slot-group').remove()" title="Remove">×</button>`;
    list.appendChild(grp);
}

// ── Time helpers ─────────────────────────────────────
function convert12To24(t) {
    if (!t) return '';
    const m = t.trim().toUpperCase().match(/(\d{1,2}):(\d{2})\s*(AM|PM)/);
    if (!m) return '';
    let h = parseInt(m[1]);
    if (m[3] === 'PM' && h !== 12) h += 12;
    else if (m[3] === 'AM' && h === 12) h = 0;
    return `${String(h).padStart(2,'0')}:${m[2]}`;
}

function fmt12(t24) {
    if (!t24) return '';
    const [h, m] = t24.split(':').map(Number);
    if (isNaN(h)) return t24;
    const p = h >= 12 ? 'PM' : 'AM';
    const d = h === 0 ? 12 : h > 12 ? h - 12 : h;
    return `${d}:${String(m).padStart(2,'0')} ${p}`;
}

// ── Validation ───────────────────────────────────────
function showValidation(msg) {
    document.getElementById('smValidationMsg').classList.remove('d-none');
    document.getElementById('smValidationText').textContent = msg;
}
function clearValidation() {
    document.getElementById('smValidationMsg').classList.add('d-none');
}

// ── Collect grid → hidden inputs, then submit ────────
document.getElementById('schedForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate
    const barangayId = document.getElementById('smLocation').value;
    if (!barangayId)    { showValidation('Please select a location.'); return; }
    if (!smType)        { showValidation('Please select a schedule type.'); return; }
    if (!smPersonnelId) { showValidation('Please select a personnel.'); return; }

    // Collect shifts
    const scheduleData = {};
    DAYS.forEach(day => {
        const slots = [];
        // Standard shifts
        ['morning','afternoon'].forEach(shift => {
            const cell = document.querySelector(`.sg-cell[data-day="${day}"][data-shift="${shift}"]`);
            if (cell && cell.classList.contains('active-' + shift)) {
                slots.push(SHIFTS[shift].label);
            }
        });
        // Custom slots
        const list = document.getElementById('customSlots_' + day);
        if (list) {
            list.querySelectorAll('.custom-slot-group').forEach(grp => {
                const s = grp.querySelector('.cs-start')?.value;
                const en = grp.querySelector('.cs-end')?.value;
                if (s && en) slots.push(`${fmt12(s)}-${fmt12(en)}`);
            });
        }
        if (slots.length) scheduleData[day] = slots;
    });

    if (Object.keys(scheduleData).length === 0) {
        showValidation('Please assign at least one shift.');
        return;
    }

    // Set hidden fields
    document.getElementById('smBarangayId').value    = barangayId;
    document.getElementById('smType').value          = smType;
    document.getElementById('smPersonnelId').value   = smPersonnelId;
    document.getElementById('smPersonnelName').value = smPersonnelName;

    const sun = new Date(currentWeekStart);
    sun.setDate(currentWeekStart.getDate() + 6);
    document.getElementById('smWeekStart').value = fmtDate(currentWeekStart);
    document.getElementById('smWeekEnd').value   = fmtDate(sun);

    // Remove old dynamic schedule inputs
    this.querySelectorAll('.dyn-sched-input').forEach(el => el.remove());

    // Inject schedule[day][] hidden inputs
    Object.entries(scheduleData).forEach(([day, slots]) => {
        slots.forEach(slot => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = `schedule[${day}][]`;
            inp.value = slot;
            inp.className = 'dyn-sched-input';
            this.appendChild(inp);
        });
    });

    // Loading state
    document.querySelector('.sm-submit-label').classList.add('d-none');
    document.querySelector('.sm-submit-loading').classList.remove('d-none');
    document.getElementById('smSubmitBtn').disabled = true;

    this.submit();
});

// ── Delete modal ─────────────────────────────────────
function confirmDelete(id, name, barangayId) {
    document.getElementById('deleteSchedName').textContent   = name;
    document.getElementById('deleteSchedForm').action        = `/rhu/schedules/${id}`;
    document.getElementById('deleteSchedBarangay').value     = barangayId;
    new bootstrap.Modal(document.getElementById('deleteSchedModal')).show();
}

// ── Reset add modal on open ───────────────────────────
function resetPersonnelArea() {
    document.getElementById('personnelScroll').innerHTML = `
        <div class="personnel-placeholder">
            <i class="bi bi-people"></i>
            <span>Select a schedule type to see available personnel</span>
        </div>`;
    document.getElementById('smPersonnelHint').textContent = 'Select a schedule type first';
}

document.addEventListener('DOMContentLoaded', function() {
    updateWeekLabel();

    // Auto-dismiss toasts
    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000);
    });

    // Reset submit button when modal is hidden
    document.getElementById('schedModal').addEventListener('hidden.bs.modal', function() {
        document.querySelector('.sm-submit-label').classList.remove('d-none');
        document.querySelector('.sm-submit-loading').classList.add('d-none');
        document.getElementById('smSubmitBtn').disabled = false;
    });
});
</script>
@endsection
