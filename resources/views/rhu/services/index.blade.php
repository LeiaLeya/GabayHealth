@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Toasts --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    @php
        $activeServices    = collect($currentServices)->filter(fn($s) => $s['is_active'] ?? true)->values();
        $suspendedServices = collect($currentServices)->filter(fn($s) => !($s['is_active'] ?? true))->values();
        $currentNames      = collect($currentServices)->pluck('name')->toArray();
        $days              = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    @endphp

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Services</h2>
            <p class="text-muted mb-0 small">Manage health center services and their availability</p>
        </div>
        <button class="btn btn-add-svc" onclick="openCustomModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Custom Service
        </button>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-grid-fill"></i></div>
                <div class="stat-info"><div class="stat-value">{{ count($currentServices) }}</div><div class="stat-label">Total Services</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info"><div class="stat-value">{{ count($activeServices) }}</div><div class="stat-label">Active</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef9c3;color:#d97706;"><i class="bi bi-pause-circle-fill"></i></div>
                <div class="stat-info"><div class="stat-value">{{ count($suspendedServices) }}</div><div class="stat-label">Suspended</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-list-check"></i></div>
                <div class="stat-info"><div class="stat-value">{{ array_sum(array_map('count', $predefinedServices)) }}</div><div class="stat-label">Available to Add</div></div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Left: Add Service Panel ── --}}
        <div class="col-lg-4">
            <div class="panel">
                <div class="panel-header">
                    <i class="bi bi-plus-circle me-2"></i>Add Service
                </div>
                <div class="panel-body">

                    {{-- Search --}}
                    <div class="svc-search-wrap mb-3">
                        <i class="bi bi-search svc-search-icon"></i>
                        <input type="text" class="svc-search-input" id="svcSearch" placeholder="Search services…" oninput="filterServices()">
                    </div>

                    {{-- Category pills --}}
                    <div class="cat-pills" id="catPills">
                        <button class="cat-pill active" data-cat="all" onclick="filterCat(this)">All</button>
                        @foreach(array_keys($predefinedServices) as $cat)
                        <button class="cat-pill" data-cat="{{ $cat }}" onclick="filterCat(this)">{{ $cat }}</button>
                        @endforeach
                    </div>

                    {{-- Services list --}}
                    <div class="predefined-list" id="predefinedList">
                        @foreach($predefinedServices as $category => $services)
                            <div class="cat-group" data-cat="{{ $category }}">
                                <div class="cat-group-label">{{ $category }}</div>
                                @foreach($services as $key => $service)
                                @php $isAdded = in_array($key, $currentNames); @endphp
                                <div class="svc-item {{ $isAdded ? 'svc-item-added' : '' }}"
                                     data-name="{{ strtolower($service) }}"
                                     data-cat="{{ $category }}"
                                     @if(!$isAdded) onclick="addPredefined('{{ $key }}','{{ addslashes($service) }}','{{ $category }}')" @endif>
                                    <div class="svc-item-info">
                                        <span class="svc-item-name">{{ $service }}</span>
                                    </div>
                                    @if($isAdded)
                                        <span class="svc-added-badge"><i class="bi bi-check2"></i> Added</span>
                                    @else
                                        <i class="bi bi-plus-circle svc-item-add-icon"></i>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @endforeach
                        <div class="svc-no-results d-none" id="noResults">
                            <i class="bi bi-search"></i><span>No services match your search</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Right: Current Services ── --}}
        <div class="col-lg-8">
            <div class="panel">
                <div class="panel-header">
                    <i class="bi bi-gear me-2"></i>Current Services
                </div>
                <div class="panel-body p-0">

                    {{-- Tabs --}}
                    <div class="svc-tabs">
                        <button class="svc-tab active" id="tabActive" onclick="switchSvcTab('active')">
                            <i class="bi bi-check-circle me-1"></i>Active
                            <span class="svc-tab-count active-count">{{ count($activeServices) }}</span>
                        </button>
                        <button class="svc-tab" id="tabSuspended" onclick="switchSvcTab('suspended')">
                            <i class="bi bi-pause-circle me-1"></i>Suspended
                            <span class="svc-tab-count suspended-count">{{ count($suspendedServices) }}</span>
                        </button>
                    </div>

                    {{-- Active pane --}}
                    <div id="paneActive" class="svc-pane p-3">
                        @if(count($activeServices) > 0)
                            <div class="svc-grid">
                                @foreach($activeServices as $service)
                                <div class="svc-card">
                                    <div class="svc-card-header svc-card-active">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="svc-card-name">{{ $service['display_name'] ?? $service['name'] }}</div>
                                            <span class="svc-cat-badge">{{ $service['category'] }}</span>
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <button class="svc-action-btn" onclick="openEditModal('{{ $service['id'] }}','{{ addslashes($service['display_name'] ?? $service['name']) }}','{{ $service['category'] }}','{{ addslashes($service['description'] ?? '') }}',{{ json_encode($service['schedule'] ?? []) }})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="svc-action-btn svc-btn-warn" onclick="openSuspendModal('{{ $service['id'] }}','{{ addslashes($service['display_name'] ?? $service['name']) }}')" title="Suspend">
                                                <i class="bi bi-pause-fill"></i>
                                            </button>
                                            <button class="svc-action-btn svc-btn-danger" onclick="openDeleteConfirm('{{ $service['id'] }}','{{ addslashes($service['display_name'] ?? $service['name']) }}','/services/{{ $service['id'] }}','Delete Service')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="svc-card-body">
                                        @if(!empty($service['description']))
                                        <p class="svc-desc">{{ $service['description'] }}</p>
                                        @endif
                                        @if(!empty($service['schedule']))
                                        <div class="svc-schedule">
                                            <div class="svc-schedule-label"><i class="bi bi-clock me-1"></i>Schedule</div>
                                            @foreach($service['schedule'] as $day => $times)
                                                @if(!empty($times))
                                                <div class="svc-day-row">
                                                    <span class="svc-day-name">{{ ucfirst($day) }}</span>
                                                    <div class="svc-time-chips">
                                                        @foreach($times as $t)
                                                        <span class="svc-time-chip">{{ $t }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="svc-no-schedule"><i class="bi bi-clock me-1"></i>No schedule set</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="svc-empty">
                                <i class="bi bi-clipboard-check"></i>
                                <div class="svc-empty-title">No Active Services</div>
                                <div class="svc-empty-text">Add services from the panel on the left.</div>
                            </div>
                        @endif
                    </div>

                    {{-- Suspended pane --}}
                    <div id="paneSuspended" class="svc-pane p-3 d-none">
                        @if(count($suspendedServices) > 0)
                            <div class="svc-grid">
                                @foreach($suspendedServices as $service)
                                <div class="svc-card svc-card-suspended-wrap">
                                    <div class="svc-card-header svc-card-suspended">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="svc-card-name">{{ $service['display_name'] ?? $service['name'] }}</div>
                                            <span class="svc-cat-badge svc-cat-badge-suspended">{{ $service['category'] }}</span>
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <button class="svc-action-btn svc-btn-resume" onclick="resumeService('{{ $service['id'] }}','{{ addslashes($service['display_name'] ?? $service['name']) }}')" title="Reactivate">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                            <button class="svc-action-btn svc-btn-danger" onclick="openDeleteConfirm('{{ $service['id'] }}','{{ addslashes($service['display_name'] ?? $service['name']) }}','/services/{{ $service['id'] }}','Delete Service')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="svc-card-body">
                                        @if(!empty($service['deactivation_reason']))
                                        <div class="svc-suspend-reason">
                                            <i class="bi bi-info-circle me-1"></i>{{ $service['deactivation_reason'] }}
                                        </div>
                                        @endif
                                        @if(!empty($service['description']))
                                        <p class="svc-desc">{{ $service['description'] }}</p>
                                        @endif
                                        @if(!empty($service['schedule']))
                                        <div class="svc-schedule">
                                            <div class="svc-schedule-label"><i class="bi bi-clock me-1"></i>Schedule</div>
                                            @foreach($service['schedule'] as $day => $times)
                                                @if(!empty($times))
                                                <div class="svc-day-row">
                                                    <span class="svc-day-name">{{ ucfirst($day) }}</span>
                                                    <div class="svc-time-chips">
                                                        @foreach($times as $t)
                                                        <span class="svc-time-chip">{{ $t }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="svc-empty">
                                <i class="bi bi-pause-circle"></i>
                                <div class="svc-empty-title">No Suspended Services</div>
                                <div class="svc-empty-text">Suspended services will appear here.</div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Add Custom Service Modal ─── --}}
<div class="modal fade" id="customServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-hd">
                <div><div class="modal-eyebrow">Custom</div><h5 class="modal-hd-title">Add Custom Service</h5></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('services.store') }}" method="POST" id="customServiceForm">
                @csrf
                <input type="hidden" name="is_custom" value="true">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="modal-field-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" name="name" required placeholder="Enter service name">
                        </div>
                        <div class="col-md-6">
                            <label class="modal-field-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" name="category" required>
                                <option value="">— Select category —</option>
                                @foreach(array_keys($predefinedServices) as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="modal-field-label">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea class="form-control rounded-3" name="description" rows="3" placeholder="Describe this service…"></textarea>
                    </div>
                    <div class="modal-section-title"><i class="bi bi-calendar3 me-2"></i>Service Schedule <span class="text-muted fw-normal" style="text-transform:none;letter-spacing:0;font-size:.75rem;">— leave unchecked for days off</span></div>
                    <div class="svc-sched-grid" id="customSchedGrid">
                        @foreach($days as $day)
                        <div class="svc-sched-row" id="customRow_{{ $day }}">
                            <label class="svc-sched-day-toggle">
                                <input type="checkbox" class="svc-day-check" data-prefix="custom" data-day="{{ $day }}" onchange="toggleSchedDay(this)">
                                <span class="svc-sched-day-name {{ in_array($day, ['saturday','sunday']) ? 'weekend' : '' }}">{{ ucfirst($day) }}</span>
                            </label>
                            <div class="svc-sched-slots d-none" id="customSlots_{{ $day }}">
                                <div class="svc-time-input-group">
                                    <input type="time" class="svc-time-in svc-t-start" data-day="{{ $day }}" onchange="syncSvcTime(this,'custom')">
                                    <span class="svc-time-sep">–</span>
                                    <input type="time" class="svc-time-in svc-t-end" data-day="{{ $day }}" onchange="syncSvcTime(this,'custom')">
                                    <input type="hidden" name="schedule[{{ $day }}][]" class="svc-t-hidden">
                                    <button type="button" class="svc-slot-remove" onclick="removeSvcSlot(this)"><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                            <button type="button" class="svc-slot-add d-none" id="customAdd_{{ $day }}" onclick="addSvcSlot('{{ $day }}','custom')"><i class="bi bi-plus"></i></button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-plus me-1"></i>Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Edit Service Modal ─── --}}
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-hd">
                <div><div class="modal-eyebrow">Edit Service</div><h5 class="modal-hd-title" id="editModalTitle">Edit Service</h5></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editServiceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="modal-field-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="modal-field-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="edit_category" name="category" required>
                                @foreach(array_keys($predefinedServices) as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="modal-field-label">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea class="form-control rounded-3" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="modal-section-title"><i class="bi bi-calendar3 me-2"></i>Service Schedule <span class="text-muted fw-normal" style="text-transform:none;letter-spacing:0;font-size:.75rem;">— leave unchecked for days off</span></div>
                    <div class="svc-sched-grid" id="editSchedGrid">
                        @foreach($days as $day)
                        <div class="svc-sched-row" id="editRow_{{ $day }}">
                            <label class="svc-sched-day-toggle">
                                <input type="checkbox" class="svc-day-check" data-prefix="edit" data-day="{{ $day }}" onchange="toggleSchedDay(this)">
                                <span class="svc-sched-day-name {{ in_array($day, ['saturday','sunday']) ? 'weekend' : '' }}">{{ ucfirst($day) }}</span>
                            </label>
                            <div class="svc-sched-slots d-none" id="editSlots_{{ $day }}"></div>
                            <button type="button" class="svc-slot-add d-none" id="editAdd_{{ $day }}" onclick="addSvcSlot('{{ $day }}','edit')"><i class="bi bi-plus"></i></button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Suspend Service Modal ─── --}}
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-hd" style="background:#d97706;">
                <div><div class="modal-eyebrow">Confirm</div><h5 class="modal-hd-title">Suspend Service</h5></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">Suspending <strong id="suspendServiceName"></strong> will hide it from residents. You can reactivate it anytime.</p>
                <label class="modal-field-label">Reason for suspension <span class="text-muted fw-normal">(Optional)</span></label>
                <textarea class="form-control rounded-3" id="suspendReason" rows="3" placeholder="e.g. Temporary staff shortage, facility maintenance…"></textarea>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 fw-semibold" onclick="confirmSuspend()">
                    <i class="bi bi-pause-fill me-1"></i>Suspend Service
                </button>
            </div>
        </div>
    </div>
</div>

@include('partials.delete-confirm-modal', ['modalId' => 'svcDeleteModal'])

<style>
*, *::before, *::after { box-sizing: border-box; }

/* ── Page ── */
.btn-add-svc { background:#1657c1; border:none; color:#fff; font-size:.82rem; font-weight:500; padding:6px 16px; border-radius:6px; transition:opacity .1s; }
.btn-add-svc:hover { opacity:.82; color:#fff; }

.stat-card { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:6px; background:#fff; border:1px solid #e9e9e7; }
.stat-icon { width:36px; height:36px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#f1f1ef; color:#787774; }
.stat-value { font-size:1.5rem; font-weight:700; color:#37352f; line-height:1; }
.stat-label { font-size:.72rem; color:#9b9b9b; font-weight:400; margin-top:2px; }

/* ── Panel ── */
.panel { background:#fff; border:1px solid #e9e9e7; border-radius:10px; overflow:hidden; }
.panel-header { background:#1657c1; color:#fff; padding:14px 18px; font-weight:600; font-size:.95rem; display:flex; align-items:center; }
.panel-body { padding:16px 18px; }

/* ── Add service panel ── */
.svc-search-wrap { position:relative; }
.svc-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.8rem; pointer-events:none; }
.svc-search-input { width:100%; padding:7px 10px 7px 30px; border:1px solid #e2e8f0; border-radius:8px; font-size:.84rem; outline:none; }
.svc-search-input:focus { border-color:#1657c1; }

.cat-pills { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px; }
.cat-pill { font-size:.72rem; font-weight:500; padding:4px 10px; border-radius:20px; border:1px solid #e2e8f0; background:#fff; color:#475569; cursor:pointer; transition:all .12s; white-space:nowrap; }
.cat-pill:hover { border-color:#94a3b8; background:#f8fafc; }
.cat-pill.active { border-color:#1657c1; background:#eff6ff; color:#1657c1; font-weight:600; }

.predefined-list { max-height:520px; overflow-y:auto; padding-right:2px; scrollbar-width:thin; scrollbar-color:#e2e8f0 transparent; }
.cat-group { margin-bottom:10px; }
.cat-group-label { font-size:.65rem; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:#94a3b8; padding:6px 4px 4px; }
.svc-item { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:7px; border:1px solid #f1f1ef; background:#fafaf9; cursor:pointer; transition:all .12s; margin-bottom:4px; }
.svc-item:hover { border-color:#1657c1; background:#eff6ff; }
.svc-item-added { background:#f1f5f9; border-color:#e2e8f0; cursor:default; opacity:.7; }
.svc-item-added:hover { border-color:#e2e8f0; background:#f1f5f9; }
.svc-item-info { flex:1; min-width:0; }
.svc-item-name { font-size:.83rem; font-weight:500; color:#374151; }
.svc-item-add-icon { color:#94a3b8; font-size:1rem; flex-shrink:0; transition:color .1s; }
.svc-item:hover .svc-item-add-icon { color:#1657c1; }
.svc-added-badge { font-size:.68rem; font-weight:600; color:#16a34a; background:#dcfce7; padding:2px 7px; border-radius:10px; white-space:nowrap; }
.svc-no-results { display:flex; flex-direction:column; align-items:center; gap:6px; padding:30px; color:#94a3b8; font-size:.82rem; text-align:center; }
.svc-no-results i { font-size:1.5rem; }

/* ── Service tabs ── */
.svc-tabs { display:flex; gap:0; border-bottom:1px solid #e9e9e7; padding:0 16px; }
.svc-tab { background:none; border:none; padding:10px 16px 12px; font-size:.85rem; font-weight:500; color:#787774; border-bottom:2px solid transparent; margin-bottom:-1px; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:6px; }
.svc-tab:hover { color:#37352f; }
.svc-tab.active { color:#37352f; border-bottom-color:#37352f; }
.svc-tab-count { font-size:.68rem; font-weight:700; padding:1px 7px; border-radius:10px; }
.active-count { background:#dcfce7; color:#16a34a; }
.suspended-count { background:#fef9c3; color:#d97706; }
.svc-tab.active .active-count { background:#16a34a; color:#fff; }
.svc-tab.active .suspended-count { background:#d97706; color:#fff; }

/* ── Service grid ── */
.svc-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:14px; }
.svc-card { border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; transition:box-shadow .15s; }
.svc-card:hover { box-shadow:0 2px 12px rgba(0,0,0,.08); }
.svc-card-suspended-wrap { opacity:.9; }

.svc-card-header { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; padding:12px 14px 10px; }
.svc-card-active { background:#1657c1; }
.svc-card-suspended { background:#6b7280; }
.svc-card-name { font-size:.88rem; font-weight:600; color:#fff; margin-bottom:4px; }
.svc-cat-badge { font-size:.62rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; display:inline-block; }
.svc-cat-badge-suspended { background:rgba(255,255,255,.12); }

.svc-action-btn { width:26px; height:26px; border-radius:4px; border:none; background:rgba(255,255,255,.12); color:rgba(255,255,255,.8); display:flex; align-items:center; justify-content:center; font-size:.75rem; cursor:pointer; transition:background .1s; flex-shrink:0; }
.svc-action-btn:hover { background:rgba(255,255,255,.25); color:#fff; }
.svc-btn-warn:hover { background:rgba(251,191,36,.35); }
.svc-btn-danger:hover { background:rgba(239,68,68,.35); color:#fecaca; }
.svc-btn-resume { background:rgba(34,197,94,.2); }
.svc-btn-resume:hover { background:rgba(34,197,94,.4); }

.svc-card-body { padding:12px 14px; }
.svc-desc { font-size:.8rem; color:#6b7280; margin-bottom:10px; line-height:1.4; }
.svc-suspend-reason { font-size:.78rem; color:#d97706; background:#fffbeb; border:1px solid #fde68a; border-radius:5px; padding:6px 10px; margin-bottom:10px; }
.svc-schedule { }
.svc-schedule-label { font-size:.68rem; font-weight:700; letter-spacing:.3px; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.svc-day-row { display:flex; align-items:center; gap:6px; margin-bottom:3px; }
.svc-day-name { font-size:.72rem; font-weight:600; color:#6b7280; min-width:64px; }
.svc-time-chips { display:flex; flex-wrap:wrap; gap:3px; }
.svc-time-chip { font-size:.68rem; background:#eff6ff; color:#1d4ed8; padding:2px 7px; border-radius:3px; }
.svc-no-schedule { font-size:.78rem; color:#94a3b8; }

.svc-empty { text-align:center; padding:50px 20px; color:#94a3b8; }
.svc-empty i { font-size:2.5rem; display:block; margin-bottom:10px; }
.svc-empty-title { font-size:.95rem; font-weight:600; color:#374151; margin-bottom:4px; }
.svc-empty-text { font-size:.82rem; }

/* ── Schedule grid in modals ── */
.svc-sched-grid { display:flex; flex-direction:column; gap:4px; }
.svc-sched-row { display:flex; align-items:flex-start; gap:10px; padding:8px 10px; background:#fafaf9; border:1px solid #f1f1ef; border-radius:7px; }
.svc-sched-day-toggle { display:flex; align-items:center; gap:8px; cursor:pointer; min-width:100px; padding-top:2px; }
.svc-sched-day-toggle input[type="checkbox"] { accent-color:#1657c1; width:15px; height:15px; flex-shrink:0; cursor:pointer; }
.svc-sched-day-name { font-size:.82rem; font-weight:600; color:#374151; }
.svc-sched-day-name.weekend { color:#94a3b8; }
.svc-sched-slots { flex:1; display:flex; flex-direction:column; gap:5px; }
.svc-time-input-group { display:flex; align-items:center; gap:6px; flex-wrap:nowrap; }
.svc-time-in { border:1px solid #e2e8f0; border-radius:5px; padding:4px 6px; font-size:.8rem; color:#374151; max-width:110px; }
.svc-time-in:focus { outline:none; border-color:#1657c1; }
.svc-time-sep { font-size:.8rem; color:#94a3b8; flex-shrink:0; }
.svc-slot-remove { width:22px; height:22px; border-radius:4px; border:none; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; font-size:.7rem; cursor:pointer; flex-shrink:0; }
.svc-slot-remove:hover { background:#fecaca; }
.svc-slot-add { width:24px; height:24px; border-radius:4px; border:none; background:#f1f1ef; color:#6b7280; display:flex; align-items:center; justify-content:center; font-size:.8rem; cursor:pointer; flex-shrink:0; align-self:center; }
.svc-slot-add:hover { background:#e2e8f0; color:#374151; }

/* ── Modal chrome ── */
.modal-hd { display:flex; align-items:flex-start; justify-content:space-between; background:#1657c1; padding:18px 22px; }
.modal-eyebrow { font-size:.64rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:5px; display:inline-block; }
.modal-hd-title { font-size:1rem; font-weight:600; color:#fff; margin:0; }
.modal-field-label { font-size:.8rem; font-weight:600; color:#374151; display:block; margin-bottom:5px; }
.modal-section-title { font-size:.7rem; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#787774; margin-bottom:10px; display:flex; align-items:center; }
.form-control, .form-select { border-radius:8px !important; border-color:#e2e8f0; font-size:.88rem; }
.form-control:focus, .form-select:focus { border-color:#1657c1; box-shadow:0 0 0 3px rgba(22,87,193,.12); }
</style>

<script>
// ── Tab switching ────────────────────────────────────
function switchSvcTab(tab) {
    document.getElementById('paneActive').classList.toggle('d-none', tab !== 'active');
    document.getElementById('paneSuspended').classList.toggle('d-none', tab !== 'suspended');
    document.getElementById('tabActive').classList.toggle('active', tab === 'active');
    document.getElementById('tabSuspended').classList.toggle('active', tab === 'suspended');
}

// ── Left panel: search + category filter ────────────
let currentCat = 'all';

function filterCat(btn) {
    currentCat = btn.dataset.cat;
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function filterServices() { applyFilters(); }

function applyFilters() {
    const q = document.getElementById('svcSearch').value.toLowerCase();
    let anyVisible = false;
    document.querySelectorAll('.cat-group').forEach(grp => {
        const cat = grp.dataset.cat;
        const catMatch = currentCat === 'all' || currentCat === cat;
        let grpHasVisible = false;
        grp.querySelectorAll('.svc-item').forEach(item => {
            const nameMatch = item.dataset.name.includes(q);
            const show = catMatch && nameMatch;
            item.style.display = show ? '' : 'none';
            if (show) { grpHasVisible = true; anyVisible = true; }
        });
        grp.style.display = grpHasVisible ? '' : 'none';
    });
    document.getElementById('noResults').classList.toggle('d-none', anyVisible);
}

// ── Add predefined service ────────────────────────────
function addPredefined(key, name, category) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("services.store") }}';
    [['_token','{{ csrf_token() }}'],['name',key],['display_name',name],['category',category],['is_custom','false']].forEach(([n,v]) => {
        const i = document.createElement('input');
        i.type='hidden'; i.name=n; i.value=v; form.appendChild(i);
    });
    document.body.appendChild(form);
    form.submit();
}

// ── Custom service modal ─────────────────────────────
function openCustomModal() {
    // Reset form
    document.getElementById('customServiceForm').reset();
    ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'].forEach(day => {
        const slots = document.getElementById('customSlots_' + day);
        if (slots) { slots.innerHTML = ''; slots.classList.add('d-none'); }
        const add = document.getElementById('customAdd_' + day);
        if (add) add.classList.add('d-none');
        const cb = document.querySelector(`#customRow_${day} .svc-day-check`);
        if (cb) cb.checked = false;
    });
    new bootstrap.Modal(document.getElementById('customServiceModal')).show();
}

// ── Edit service ─────────────────────────────────────
function openEditModal(id, name, category, description, schedule) {
    document.getElementById('editModalTitle').textContent = name;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_description').value = description;
    document.getElementById('editServiceForm').action = `/services/${id}`;

    const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    days.forEach(day => {
        const slots = document.getElementById('editSlots_' + day);
        const add   = document.getElementById('editAdd_' + day);
        const cb    = document.querySelector(`#editRow_${day} .svc-day-check`);
        slots.innerHTML = '';
        slots.classList.add('d-none');
        add.classList.add('d-none');
        cb.checked = false;

        if (schedule && schedule[day] && schedule[day].length > 0) {
            cb.checked = true;
            slots.classList.remove('d-none');
            add.classList.remove('d-none');
            schedule[day].forEach(slot => {
                const {start, end} = parseSlot(slot);
                appendSvcSlot(slots, day, 'edit', start, end, slot);
            });
        }
    });

    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
}

// ── Suspend service ───────────────────────────────────
let pendingSuspendId = null;
function openSuspendModal(id, name) {
    pendingSuspendId = id;
    document.getElementById('suspendServiceName').textContent = name;
    document.getElementById('suspendReason').value = '';
    new bootstrap.Modal(document.getElementById('suspendModal')).show();
}
function confirmSuspend() {
    const reason = document.getElementById('suspendReason').value.trim();
    submitToggle(pendingSuspendId, false, reason);
}

// ── Resume service ────────────────────────────────────
function resumeService(id) {
    submitToggle(id, true, '');
}

function submitToggle(id, status, reason) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/services/${id}/toggle-status`;
    [['_token','{{ csrf_token() }}'],['_method','PATCH']].forEach(([n,v]) => {
        const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; form.appendChild(i);
    });
    if (!status) {
        const r = document.createElement('input'); r.type='hidden'; r.name='deactivation_reason'; r.value=reason; form.appendChild(r);
    }
    document.body.appendChild(form);
    form.submit();
}

// ── Delete service ────────────────────────────────────
// delete handled by partials/delete-confirm-modal

// ── Schedule day toggle ───────────────────────────────
function toggleSchedDay(cb) {
    const day    = cb.dataset.day;
    const prefix = cb.dataset.prefix;
    const slots  = document.getElementById(prefix + 'Slots_' + day);
    const add    = document.getElementById(prefix + 'Add_' + day);

    if (cb.checked) {
        slots.classList.remove('d-none');
        add.classList.remove('d-none');
        if (!slots.querySelector('.svc-time-input-group')) {
            appendSvcSlot(slots, day, prefix, '', '', '');
        }
    } else {
        slots.classList.add('d-none');
        add.classList.add('d-none');
        slots.innerHTML = '';
    }
}

function addSvcSlot(day, prefix) {
    const slots = document.getElementById(prefix + 'Slots_' + day);
    appendSvcSlot(slots, day, prefix, '', '', '');
}

function appendSvcSlot(container, day, prefix, start24, end24, rawValue) {
    const div = document.createElement('div');
    div.className = 'svc-time-input-group';
    div.innerHTML = `
        <input type="time" class="svc-time-in svc-t-start" data-day="${day}" value="${start24}" onchange="syncSvcTime(this,'${prefix}')">
        <span class="svc-time-sep">–</span>
        <input type="time" class="svc-time-in svc-t-end"   data-day="${day}" value="${end24}"   onchange="syncSvcTime(this,'${prefix}')">
        <input type="hidden" name="schedule[${day}][]" class="svc-t-hidden" value="${rawValue}">
        <button type="button" class="svc-slot-remove" onclick="removeSvcSlot(this)"><i class="bi bi-x"></i></button>`;
    container.appendChild(div);
}

function removeSvcSlot(btn) {
    const grp  = btn.closest('.svc-time-input-group');
    const cont = grp.closest('.svc-sched-slots');
    grp.remove();
    if (!cont.querySelector('.svc-time-input-group')) {
        cont.classList.add('d-none');
        // uncheck the day
        const row = cont.closest('.svc-sched-row');
        if (row) { const cb = row.querySelector('.svc-day-check'); if (cb) cb.checked = false; }
        const addBtn = cont.nextElementSibling;
        if (addBtn && addBtn.classList.contains('svc-slot-add')) addBtn.classList.add('d-none');
    }
}

function syncSvcTime(input) {
    const grp   = input.closest('.svc-time-input-group');
    const start = grp.querySelector('.svc-t-start').value;
    const end   = grp.querySelector('.svc-t-end').value;
    const hidden = grp.querySelector('.svc-t-hidden');
    if (hidden) hidden.value = start && end ? `${fmt12(start)}-${fmt12(end)}` : '';
}

// ── Time helpers ─────────────────────────────────────
function fmt12(t24) {
    if (!t24) return '';
    const [h, m] = t24.split(':').map(Number);
    if (isNaN(h)) return t24;
    const p = h >= 12 ? 'PM' : 'AM';
    const d = h === 0 ? 12 : h > 12 ? h - 12 : h;
    return `${d}:${String(m).padStart(2,'0')} ${p}`;
}

function parse12(str) {
    if (!str) return '';
    str = str.trim().toUpperCase().replace(/\s/g,'');
    const m = str.match(/^(\d{1,2})(?::(\d{2}))?(AM|PM)$/);
    if (!m) return '';
    let h = parseInt(m[1]);
    const min = m[2] || '00';
    if (m[3] === 'PM' && h !== 12) h += 12;
    else if (m[3] === 'AM' && h === 12) h = 0;
    return `${String(h).padStart(2,'0')}:${min}`;
}

function parseSlot(slot) {
    if (!slot) return { start:'', end:'' };
    // "8:00 AM-12:00 PM" or "9AM-11AM"
    const norm = slot.replace(/\s/g,'');
    const idx  = norm.lastIndexOf('-');
    if (idx < 1) return { start:'', end:'' };
    return { start: parse12(norm.slice(0, idx)), end: parse12(norm.slice(idx + 1)) };
}

// ── Init ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000);
    });
});
</script>
@endsection
