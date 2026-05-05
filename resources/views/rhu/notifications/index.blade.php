@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Toast Flash --}}
    @if(session('success') || session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1100;">
        <div id="flashToast" class="toast show align-items-center border-0 {{ session('success') ? 'text-bg-success' : 'text-bg-danger' }}" role="alert">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi {{ session('success') ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                    {{ session('success') ?? session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pt-2 flex-wrap gap-2">
        <div>
            <p class="text-muted mb-0" style="font-size:.78rem;letter-spacing:.04em;text-transform:uppercase;font-weight:600;">RHU</p>
            <h1 class="fw-bold mb-0" style="font-size:1.45rem;color:#37352f;">Notifications</h1>
        </div>
        <button class="btn btn-notif-add d-flex align-items-center gap-1" onclick="openWizard()">
            <i class="bi bi-plus" style="font-size:.95rem;"></i>
            <span style="font-size:.82rem;">Create Notification</span>
        </button>
    </div>

    {{-- Summary Stat Cards --}}
    @php
        $totalSent    = count($notifications);
        $thisWeek     = collect($notifications)->filter(fn($n) => isset($n['createdAt']) && \Carbon\Carbon::parse($n['createdAt'])->isCurrentWeek())->count();
        $healthAlerts = collect($notifications)->where('notification_type', 'health_alert')->count();
        $barangayCount = count($barangays);
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card--blue">
                <div class="stat-card-icon"><i class="bi bi-send-fill"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value">{{ $totalSent }}</div>
                    <div class="stat-card-label">Total Sent</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card--indigo">
                <div class="stat-card-icon"><i class="bi bi-calendar-week-fill"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value">{{ $thisWeek }}</div>
                    <div class="stat-card-label">This Week</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card--red">
                <div class="stat-card-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value">{{ $healthAlerts }}</div>
                    <div class="stat-card-label">Health Alerts</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-card--green">
                <div class="stat-card-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value">{{ $barangayCount }}</div>
                    <div class="stat-card-label">Barangays</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notification History List --}}
    <div class="notif-list-card">
        <div class="notif-list-header d-flex align-items-center justify-content-between px-4 py-3">
            <div>
                <span class="notif-list-title">Notification History</span>
                <span class="notif-list-meta ms-2">{{ $totalSent }} {{ $totalSent === 1 ? 'record' : 'records' }}</span>
            </div>
            {{-- Search --}}
            <div class="notif-search-wrap">
                <i class="bi bi-search notif-search-icon"></i>
                <input type="text" class="notif-search-input" id="notifSearch" placeholder="Search notifications…" oninput="filterNotifications(this.value)">
            </div>
        </div>

        @if($totalSent > 0)
        <div class="table-responsive">
            <table class="table notif-table mb-0" id="notifTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Target Barangays</th>
                        <th>Audience</th>
                        <th>Date Sent</th>
                        <th>Status</th>
                        <th style="width:48px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                    @php
                        $nt = $notification['notification_type'] ?? 'announcement';
                        $typeMap = [
                            'health_alert'           => ['label' => 'Health Alert',   'class' => 'nbadge-danger'],
                            'announcement'           => ['label' => 'Announcement',    'class' => 'nbadge-primary'],
                            'reminder'               => ['label' => 'Reminder',        'class' => 'nbadge-warning'],
                            'vaccination_update'     => ['label' => 'Vaccination',     'class' => 'nbadge-success'],
                            'clinic_schedule_update' => ['label' => 'Clinic Schedule', 'class' => 'nbadge-info'],
                        ];
                        $tm      = $typeMap[$nt] ?? ['label' => 'Notification', 'class' => 'nbadge-primary'];
                        $status  = $notification['status'] ?? 'sent';
                        $date    = $notification['createdAt'] ?? '';
                        $fmtDate = $date ? \Carbon\Carbon::parse($date)->format('M d, Y') : '—';
                        $fmtTime = $date ? \Carbon\Carbon::parse($date)->format('h:i A') : '';
                        $targets = $notification['target_barangays'] ?? [];
                        $targetStr = is_array($targets) ? implode(', ', array_slice($targets, 0, 2)) . (count($targets) > 2 ? ' +' . (count($targets) - 2) . ' more' : '') : ($targets ?: '—');
                        $audienceMap = [
                            'all_residents'      => 'All Residents',
                            'specific_purok'     => 'Specific Purok',
                            'specific_age_group' => 'Age Group',
                            'pregnant_women'     => 'Pregnant Women',
                            'senior_citizens'    => 'Senior Citizens',
                            'children_0_12'      => 'Children (0–12)',
                        ];
                        $audienceLabel = $audienceMap[$notification['target_audience'] ?? ''] ?? '—';
                        $notifJson = json_encode([
                            'type'       => $tm['label'],
                            'typeClass'  => $tm['class'],
                            'title'      => $notification['title'] ?? 'Untitled',
                            'message'    => $notification['message'] ?? '',
                            'targets'    => is_array($targets) ? implode(', ', $targets) : ($targets ?: '—'),
                            'audience'   => $audienceLabel,
                            'date'       => $fmtDate . ($fmtTime ? ' · ' . $fmtTime : ''),
                            'status'     => ucfirst($status),
                            'statusClass'=> $status === 'sent' ? 'nstatus-sent' : 'nstatus-pending',
                        ]);
                    @endphp
                    <tr class="notif-row"
                        onclick="openDetail({{ $notifJson }})"
                        data-search="{{ strtolower(($notification['title'] ?? '') . ' ' . (is_array($targets) ? implode(' ', $targets) : $targets) . ' ' . $tm['label']) }}">
                        <td><span class="nbadge {{ $tm['class'] }}">{{ $tm['label'] }}</span></td>
                        <td>
                            <div class="fw-semibold notif-title-cell">{{ $notification['title'] ?? 'Untitled' }}</div>
                            <div class="notif-message-preview">{{ Str::limit($notification['message'] ?? '', 60) }}</div>
                        </td>
                        <td>
                            <span class="notif-targets">{{ $targetStr }}</span>
                        </td>
                        <td><span style="font-size:.82rem;color:#787774;">{{ $audienceLabel }}</span></td>
                        <td>
                            <div style="font-size:.85rem;color:#37352f;font-weight:500;">{{ $fmtDate }}</div>
                            <div style="font-size:.72rem;color:#9b9b9b;">{{ $fmtTime }}</div>
                        </td>
                        <td>
                            <span class="nstatus {{ $status === 'sent' ? 'nstatus-sent' : 'nstatus-pending' }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td onclick="event.stopPropagation()">
                            <form action="{{ route('notifications.destroy', $notification['id']) }}" method="POST"
                                  onsubmit="return confirm('Delete this notification?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="notif-del-btn" title="Delete">
                                    <i class="bi bi-trash" style="font-size:.75rem;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="notif-empty">
            <i class="bi bi-bell-slash notif-empty-icon"></i>
            <div class="notif-empty-title">No notifications sent yet</div>
            <div class="notif-empty-text">Create your first notification using the button above.</div>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- Create Notification Wizard Modal                        --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="wizardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">

            {{-- Modal Header --}}
            <div class="modal-header-custom d-flex align-items-start justify-content-between">
                <div>
                    <div class="modal-type-badge mb-1">NOTIFICATIONS</div>
                    <div class="modal-title-custom">Create Notification</div>
                </div>
                <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal" onclick="resetWizard()"></button>
            </div>

            {{-- Step Indicator --}}
            <div class="wizard-steps-bar px-4 py-3">
                <div class="wizard-steps">
                    <div class="wizard-step active" id="stepIndicator1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Who</div>
                    </div>
                    <div class="wizard-step-line"></div>
                    <div class="wizard-step" id="stepIndicator2">
                        <div class="step-circle">2</div>
                        <div class="step-label">What</div>
                    </div>
                    <div class="wizard-step-line"></div>
                    <div class="wizard-step" id="stepIndicator3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
            </div>

            <form id="notificationForm" action="{{ route('notifications.store') }}" method="POST" novalidate>
                @csrf
                <div class="modal-body p-4">

                    {{-- ── STEP 1: Who ── --}}
                    <div class="wizard-pane" id="wizardStep1">
                        {{-- Target Barangays --}}
                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title mb-3">Target Barangay(s) <span class="text-danger">*</span></div>
                            @if(count($barangays) > 0)
                            <div class="bgy-dropdown" id="bgyDropdown">
                                <button type="button" class="bgy-trigger" id="bgyTrigger" onclick="toggleBgyDropdown()">
                                    <i class="bi bi-geo-alt me-2" style="color:#9b9b9b;"></i>
                                    <span id="bgyTriggerText">Select barangay(s)…</span>
                                    <i class="bi bi-chevron-down ms-auto bgy-chevron" id="bgyChevron"></i>
                                </button>
                                <div class="bgy-panel" id="bgyPanel">
                                    <div class="bgy-search-wrap">
                                        <i class="bi bi-search bgy-search-icon"></i>
                                        <input type="text" class="bgy-search-input" id="bgySearchInput" placeholder="Search barangay…" oninput="filterBgy(this.value)" autocomplete="off">
                                    </div>
                                    <div class="bgy-select-all-row">
                                        <label class="bgy-option bgy-select-all-label">
                                            <input type="checkbox" id="selectAllBarangays" onchange="toggleSelectAll(this)">
                                            <span class="bgy-option-name fw-semibold">Select All</span>
                                            <span class="bgy-count-badge" id="bgyCountBadge">{{ count($barangays) }}</span>
                                        </label>
                                    </div>
                                    <div class="bgy-options-list" id="bgyOptionsList">
                                        @foreach($barangays as $barangay)
                                        <label class="bgy-option" data-name="{{ strtolower($barangay['name']) }}">
                                            <input type="checkbox" name="target_barangays[]" value="{{ $barangay['id'] }}" class="barangay-checkbox" onchange="onBgyChange()">
                                            <i class="bi bi-geo-alt-fill bgy-icon"></i>
                                            <span class="bgy-option-name">{{ $barangay['name'] }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="bgy-tags" id="bgyTags"></div>
                            @else
                            <div class="empty-bgy-notice">
                                <i class="bi bi-info-circle me-2"></i>No barangays found under this RHU.
                            </div>
                            @endif
                        </div>

                        {{-- Target Audience --}}
                        <div class="modal-form-section">
                            <div class="modal-section-title mb-3">Target Audience <span class="text-danger">*</span></div>
                            <div class="row g-2">
                                @php
                                    $audiences = [
                                        'all_residents'      => ['icon' => 'bi-people-fill',     'label' => 'All Residents'],
                                        'specific_purok'     => ['icon' => 'bi-pin-map-fill',     'label' => 'Specific Purok'],
                                        'specific_age_group' => ['icon' => 'bi-bar-chart-fill',   'label' => 'Age Group'],
                                        'pregnant_women'     => ['icon' => 'bi-heart-pulse-fill', 'label' => 'Pregnant Women'],
                                        'senior_citizens'    => ['icon' => 'bi-person-cane',      'label' => 'Senior Citizens'],
                                        'children_0_12'      => ['icon' => 'bi-emoji-smile-fill', 'label' => 'Children (0–12)'],
                                    ];
                                @endphp
                                @foreach($audiences as $val => $meta)
                                <div class="col-6 col-md-4">
                                    <label class="audience-card">
                                        <input type="radio" name="target_audience" value="{{ $val }}" onchange="onAudienceChange(this)" required>
                                        <i class="bi {{ $meta['icon'] }} audience-icon"></i>
                                        <span class="audience-name">{{ $meta['label'] }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div id="purokField" class="mt-3 d-none">
                                <label class="inv-form-label">Purok Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" id="target_purok" name="target_purok" placeholder="e.g. Purok Sunflower" oninput="validateStep1()">
                            </div>
                            <div id="ageGroupField" class="mt-3 d-none">
                                <label class="inv-form-label">Age Group <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" id="target_age_group" name="target_age_group" placeholder="e.g. 18–30" oninput="validateStep1()">
                            </div>
                        </div>
                    </div>

                    {{-- ── STEP 2: What ── --}}
                    <div class="wizard-pane d-none" id="wizardStep2">
                        {{-- Notification Type --}}
                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title mb-3">Notification Type <span class="text-danger">*</span></div>
                            <input type="hidden" name="notification_type" id="notification_type">
                            <div class="type-pill-row">
                                @php
                                    $types = [
                                        'health_alert'           => ['icon' => 'bi-exclamation-triangle-fill', 'label' => 'Health Alert',   'color' => 'type-danger'],
                                        'announcement'           => ['icon' => 'bi-megaphone-fill',             'label' => 'Announcement',    'color' => 'type-primary'],
                                        'reminder'               => ['icon' => 'bi-bell-fill',                  'label' => 'Reminder',        'color' => 'type-warning'],
                                        'vaccination_update'     => ['icon' => 'bi-capsule-pill',               'label' => 'Vaccination',     'color' => 'type-success'],
                                        'clinic_schedule_update' => ['icon' => 'bi-hospital-fill',              'label' => 'Clinic Schedule', 'color' => 'type-info'],
                                    ];
                                @endphp
                                @foreach($types as $val => $meta)
                                <button type="button" class="type-pill {{ $meta['color'] }}" data-value="{{ $val }}" onclick="selectType(this)">
                                    <i class="bi {{ $meta['icon'] }} type-pill-icon"></i>
                                    <span>{{ $meta['label'] }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Title & Message --}}
                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title mb-3">Content</div>
                            <div class="mb-3">
                                <label class="inv-form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" id="title" name="title" required maxlength="255" placeholder="Notification title…" oninput="validateStep2()">
                            </div>
                            <div>
                                <label class="inv-form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control inv-input" id="message" name="message" rows="5" required maxlength="2000" placeholder="Write your message here…" oninput="onMessageInput()"></textarea>
                                <div class="d-flex justify-content-end mt-1">
                                    <span style="font-size:.72rem;color:#9b9b9b;" id="charCount">0 / 2000</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ── STEP 3: Review & Send ── --}}
                    <div class="wizard-pane d-none" id="wizardStep3">
                        <div class="review-header mb-3">
                            <i class="bi bi-clipboard-check review-header-icon"></i>
                            <div>
                                <div style="font-size:.95rem;font-weight:700;color:#37352f;">Review before sending</div>
                                <div style="font-size:.78rem;color:#9b9b9b;">Double-check all details before your notification goes out.</div>
                            </div>
                        </div>

                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title mb-3">Who receives this?</div>
                            <div class="review-row">
                                <span class="review-label">Barangay(s)</span>
                                <span class="review-value" id="reviewBarangays">—</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Audience</span>
                                <span class="review-value" id="reviewAudience">—</span>
                            </div>
                            <div class="review-row" id="reviewPurokRow" style="display:none;">
                                <span class="review-label">Purok</span>
                                <span class="review-value" id="reviewPurok">—</span>
                            </div>
                            <div class="review-row" id="reviewAgeRow" style="display:none;">
                                <span class="review-label">Age Group</span>
                                <span class="review-value" id="reviewAge">—</span>
                            </div>
                        </div>

                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title mb-3">What is being sent?</div>
                            <div class="review-row">
                                <span class="review-label">Type</span>
                                <span class="review-value" id="reviewType">—</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Title</span>
                                <span class="review-value fw-semibold" id="reviewTitle">—</span>
                            </div>
                            <div class="review-row align-items-start">
                                <span class="review-label" style="padding-top:2px;">Message</span>
                                <span class="review-value" id="reviewMessage" style="white-space:pre-wrap;line-height:1.5;">—</span>
                            </div>
                        </div>

                        {{-- Health Alert Warning --}}
                        <div id="healthAlertWarning" class="health-alert-warning d-none">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            You are sending a <strong>Health Alert</strong>. This will be delivered immediately to all registered residents. Make sure the information is accurate.
                        </div>
                    </div>

                </div>

                {{-- Wizard Footer --}}
                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex justify-content-between align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 d-none" id="wizardBackBtn" onclick="goBack()">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" onclick="resetWizard()">Cancel</button>
                        <button type="button" class="btn btn-dark rounded-pill px-4" id="wizardNextBtn" onclick="goNext()" disabled>
                            Next <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-notif-add rounded-pill px-4 d-none" id="wizardSendBtn">
                            <span class="send-label"><i class="bi bi-send-fill me-1"></i>Send Notification</span>
                            <span class="send-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Sending…</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- Notification Detail Modal                              --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom d-flex align-items-start justify-content-between">
                <div>
                    <div class="modal-type-badge mb-1" id="detailTypeBadge">NOTIFICATION</div>
                    <div class="modal-title-custom" id="detailTitle">—</div>
                </div>
                <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-form-section mb-3">
                    <div class="modal-section-title mb-3">Recipients</div>
                    <div class="review-row">
                        <span class="review-label">Barangay(s)</span>
                        <span class="review-value" id="detailBarangays">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Audience</span>
                        <span class="review-value" id="detailAudience">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Date Sent</span>
                        <span class="review-value" id="detailDate">—</span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Status</span>
                        <span id="detailStatus">—</span>
                    </div>
                </div>
                <div class="modal-form-section mb-3">
                    <div class="modal-section-title mb-3">Message</div>
                    <p id="detailMessage" style="font-size:.88rem;color:#37352f;white-space:pre-wrap;line-height:1.6;margin:0;">—</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Notion tokens ── */
.btn-notif-add {
    background:#1657c1; color:#fff; border:none;
    border-radius:6px; font-weight:500; padding:7px 16px;
    transition:background .15s;
}
.btn-notif-add:hover { background:#1249a8; color:#fff; }

.stat-card { display:flex;align-items:center;gap:14px;padding:16px 20px;border-radius:10px;background:#fff;border:1px solid #e9e9e7;transition:box-shadow .15s; }
.stat-card:hover { box-shadow:0 2px 10px rgba(0,0,0,.07); }
.stat-card-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
.stat-card-body { min-width:0; }
.stat-card-value { font-size:1.5rem;font-weight:700;line-height:1;color:#37352f; }
.stat-card-label { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#9b9b9b;margin-top:4px; }
.stat-card--blue   .stat-card-icon { background:#dde9ff;color:#1657c1; }
.stat-card--indigo .stat-card-icon { background:#ede9fe;color:#6d28d9; }
.stat-card--red    .stat-card-icon { background:#fee2e2;color:#b91c1c; }
.stat-card--green  .stat-card-icon { background:#dcfce7;color:#166534; }

/* ── Notification list card ── */
.notif-list-card { border:1px solid #e9e9e7; border-radius:8px; background:#fff; }
.notif-list-header { border-bottom:1px solid #f1f1ef; }
.notif-list-title { font-size:.82rem; font-weight:600; color:#37352f; }
.notif-list-meta  { font-size:.75rem; color:#9b9b9b; }

.notif-search-wrap { position:relative; }
.notif-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9b9b9b; font-size:.8rem; pointer-events:none; }
.notif-search-input {
    padding:6px 12px 6px 30px; border:1px solid #e9e9e7; border-radius:6px;
    font-size:.82rem; color:#37352f; outline:none; width:220px;
}
.notif-search-input:focus { border-color:#1657c1; box-shadow:0 0 0 3px rgba(22,87,193,.08); }

/* ── Notification table ── */
.notif-table { font-size:.88rem; }
.notif-table thead th { background:#f8fafc; color:#6b7280; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.4px; border-bottom:1px solid #e9e9e7; padding:10px 16px; }
.notif-table tbody td { border-bottom:1px solid #f5f5f4; padding:13px 16px; vertical-align:middle; color:#37352f; background:#fff; }
.notif-table tbody tr:last-child td { border-bottom:none; }
.notif-row { cursor:pointer; transition:background .1s; }
.notif-row:hover td { background:#fafaf9; }

.notif-title-cell { font-size:.88rem; color:#37352f; }
.notif-message-preview { font-size:.75rem; color:#9b9b9b; margin-top:2px; }
.notif-targets { font-size:.8rem; color:#787774; }

/* ── N badges ── */
.nbadge { display:inline-flex; align-items:center; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.3px; border-radius:4px; padding:2px 8px; white-space:nowrap; }
.nbadge-danger  { background:#fee2e2; color:#b91c1c; }
.nbadge-primary { background:#dde9ff; color:#1657c1; }
.nbadge-warning { background:#fef9c3; color:#a16207; }
.nbadge-success { background:#dcfce7; color:#15803d; }
.nbadge-info    { background:#cffafe; color:#0e7490; }

/* ── N status ── */
.nstatus { display:inline-flex; align-items:center; font-size:.72rem; font-weight:600; border-radius:4px; padding:2px 8px; }
.nstatus-sent    { background:#dcfce7; color:#15803d; }
.nstatus-pending { background:#fef9c3; color:#a16207; }

.notif-del-btn { width:28px; height:28px; border-radius:5px; border:none; background:#f1f1ef; color:#787774; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:background .12s, color .12s; }
.notif-del-btn:hover { background:#fee2e2; color:#b91c1c; }

.notif-empty { border:1px dashed #e9e9e7; border-radius:8px; padding:56px 24px; text-align:center; background:#fafaf9; margin:24px; }
.notif-empty-icon { font-size:2rem; color:#d4d4d0; display:block; margin-bottom:12px; }
.notif-empty-title { font-size:.95rem; font-weight:600; color:#787774; margin-bottom:4px; }
.notif-empty-text  { font-size:.82rem; color:#9b9b9b; }

/* ── Wizard modal ── */
.modal-header-custom { background:#1657c1; padding:18px 22px; }
.modal-type-badge { background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); font-size:.64rem; font-weight:700; letter-spacing:.6px; text-transform:uppercase; border-radius:4px; padding:2px 8px; display:inline-block; }
.modal-title-custom { font-size:1.05rem; font-weight:700; color:#fff; margin-top:4px; }
.modal-form-section { background:#fafaf9; border-radius:6px; padding:16px; border:1px solid #f1f1ef; }
.modal-section-title { font-size:.72rem; font-weight:700; color:#9b9b9b; text-transform:uppercase; letter-spacing:.5px; }

/* ── Step indicator ── */
.wizard-steps-bar { background:#f8fafc; border-bottom:1px solid #f1f1ef; }
.wizard-steps { display:flex; align-items:center; justify-content:center; gap:0; }
.wizard-step { display:flex; flex-direction:column; align-items:center; gap:4px; }
.wizard-step-line { flex:1; height:2px; background:#e9e9e7; margin:0 8px; width:60px; margin-bottom:16px; }
.step-circle {
    width:28px; height:28px; border-radius:50%;
    background:#e9e9e7; color:#9b9b9b;
    display:flex; align-items:center; justify-content:center;
    font-size:.75rem; font-weight:700; transition:all .2s;
}
.step-label { font-size:.7rem; font-weight:600; color:#9b9b9b; text-transform:uppercase; letter-spacing:.4px; transition:color .2s; }
.wizard-step.active .step-circle { background:#1657c1; color:#fff; }
.wizard-step.active .step-label  { color:#1657c1; }
.wizard-step.done .step-circle   { background:#15803d; color:#fff; }
.wizard-step.done .step-label    { color:#15803d; }

/* ── Barangay dropdown ── */
.bgy-dropdown { position:relative; }
.bgy-trigger { width:100%; display:flex; align-items:center; gap:6px; padding:9px 12px; border:1px solid #e9e9e7; border-radius:6px; background:#fff; font-size:.85rem; color:#37352f; cursor:pointer; transition:border-color .15s; text-align:left; }
.bgy-trigger:hover, .bgy-trigger.open { border-color:#1657c1; }
.bgy-chevron { font-size:.7rem; transition:transform .2s; color:#9b9b9b; }
.bgy-trigger.open .bgy-chevron { transform:rotate(180deg); }
.bgy-panel { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:300; background:#fff; border:1px solid #e9e9e7; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.1); display:none; max-height:260px; overflow:hidden; flex-direction:column; }
.bgy-panel.open { display:flex; }
.bgy-search-wrap { position:relative; padding:10px 10px 6px; flex-shrink:0; }
.bgy-search-icon { position:absolute; left:18px; top:50%; transform:translateY(-50%); color:#9b9b9b; font-size:.8rem; pointer-events:none; margin-top:2px; }
.bgy-search-input { width:100%; padding:6px 10px 6px 30px; border:1px solid #e9e9e7; border-radius:6px; font-size:.83rem; outline:none; }
.bgy-search-input:focus { border-color:#1657c1; }
.bgy-select-all-row { padding:4px 10px; border-bottom:1px solid #f1f1ef; flex-shrink:0; }
.bgy-select-all-label { display:flex; align-items:center; gap:8px; padding:6px 4px; cursor:pointer; border-radius:5px; }
.bgy-select-all-label:hover { background:#f8fafc; }
.bgy-count-badge { margin-left:auto; font-size:.68rem; background:#f1f1ef; color:#787774; padding:1px 7px; border-radius:10px; font-weight:600; }
.bgy-options-list { overflow-y:auto; flex:1; padding:6px 10px 8px; }
.bgy-option { display:flex; align-items:center; gap:8px; padding:7px 6px; cursor:pointer; border-radius:6px; font-size:.85rem; color:#37352f; transition:background .1s; }
.bgy-option:hover { background:#f8fafc; }
.bgy-option input[type="checkbox"] { accent-color:#1657c1; flex-shrink:0; width:15px; height:15px; cursor:pointer; }
.bgy-option.hidden { display:none; }
.bgy-icon { color:#1657c1; font-size:.78rem; flex-shrink:0; }
.bgy-option-name { flex:1; }
.bgy-tags { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.bgy-tag { display:inline-flex; align-items:center; gap:5px; background:#dde9ff; color:#1657c1; border:1px solid #bfdbfe; font-size:.75rem; font-weight:500; padding:3px 10px 3px 8px; border-radius:20px; }
.bgy-tag-remove { background:none; border:none; color:#93c5fd; padding:0; cursor:pointer; font-size:.8rem; line-height:1; display:flex; align-items:center; }
.bgy-tag-remove:hover { color:#1657c1; }
.empty-bgy-notice { padding:10px 14px; background:#fef9c3; border:1px solid #fde047; border-radius:6px; font-size:.85rem; color:#854d0e; }

/* ── Audience cards ── */
.audience-card { display:flex; flex-direction:column; align-items:center; gap:7px; padding:12px 8px; border:1.5px solid #e9e9e7; border-radius:8px; background:#fff; cursor:pointer; transition:all .15s; text-align:center; width:100%; font-size:.8rem; }
.audience-card:hover { border-color:#9b9b9b; background:#fafaf9; }
.audience-card:has(input:checked) { border-color:#1657c1; background:#dde9ff; }
.audience-card input { display:none; }
.audience-icon { font-size:1.3rem; color:#9b9b9b; }
.audience-card:has(input:checked) .audience-icon { color:#1657c1; }
.audience-name { font-weight:500; color:#37352f; line-height:1.2; }
.audience-card:has(input:checked) .audience-name { color:#1657c1; }

/* ── Type pills ── */
.type-pill-row { display:flex; flex-wrap:wrap; gap:8px; }
.type-pill { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:20px; border:1.5px solid #e9e9e7; background:#fff; font-size:.82rem; font-weight:500; color:#787774; cursor:pointer; transition:all .15s; white-space:nowrap; }
.type-pill:hover { border-color:#9b9b9b; background:#fafaf9; }
.type-pill-icon { font-size:.85rem; }
.type-pill.type-danger.active   { border-color:#b91c1c; background:#fee2e2; color:#b91c1c; }
.type-pill.type-primary.active  { border-color:#1657c1; background:#dde9ff; color:#1657c1; }
.type-pill.type-warning.active  { border-color:#a16207; background:#fef9c3; color:#a16207; }
.type-pill.type-success.active  { border-color:#15803d; background:#dcfce7; color:#15803d; }
.type-pill.type-info.active     { border-color:#0e7490; background:#cffafe; color:#0e7490; }


/* ── Review step ── */
.review-header { display:flex; align-items:center; gap:12px; padding:14px 16px; background:#fafaf9; border:1px solid #f1f1ef; border-radius:6px; margin-bottom:16px; }
.review-header-icon { font-size:1.4rem; color:#1657c1; flex-shrink:0; }
.review-row { display:flex; gap:12px; padding:8px 0; border-bottom:1px solid #f5f5f4; font-size:.85rem; }
.review-row:last-child { border-bottom:none; }
.review-label { min-width:90px; font-size:.75rem; font-weight:600; color:#9b9b9b; text-transform:uppercase; letter-spacing:.3px; padding-top:1px; flex-shrink:0; }
.review-value { color:#37352f; flex:1; }

.health-alert-warning { background:#fee2e2; border:1px solid #fca5a5; border-radius:6px; padding:12px 14px; font-size:.82rem; color:#b91c1c; margin-top:12px; }

.inv-form-label { font-size:.78rem; font-weight:600; color:#37352f; margin-bottom:4px; display:block; }
.inv-input { border:1px solid #e9e9e7; border-radius:6px; font-size:.85rem; color:#37352f; background:#fff; }
.inv-input:focus { border-color:#1657c1; box-shadow:0 0 0 3px rgba(22,87,193,.1); }
.send-loading { display:flex; align-items:center; }
</style>

<script>
// ── Wizard state ─────────────────────────────────────
let currentStep = 1;
let wizardModalInstance = null;

function openWizard() {
    if (!wizardModalInstance) {
        wizardModalInstance = new bootstrap.Modal(document.getElementById('wizardModal'));
    }
    resetWizard();
    wizardModalInstance.show();
}

function resetWizard() {
    currentStep = 1;
    showStep(1);
    document.getElementById('notificationForm').reset();
    document.querySelectorAll('.type-pill').forEach(p => p.classList.remove('active'));
    document.getElementById('notification_type').value = '';
    document.getElementById('bgyTags').innerHTML = '';
    document.getElementById('bgyTriggerText').textContent = 'Select barangay(s)…';
    document.querySelectorAll('.barangay-checkbox').forEach(c => c.checked = false);
    const selectAll = document.getElementById('selectAllBarangays');
    if (selectAll) selectAll.checked = false;
    document.getElementById('purokField').classList.add('d-none');
    document.getElementById('ageGroupField').classList.add('d-none');
    document.getElementById('charCount').textContent = '0 / 2000';
    document.getElementById('healthAlertWarning').classList.add('d-none');
    bgyPanel.classList.remove('open');
    bgyTrigger.classList.remove('open');
    validateStep1();
}

function showStep(step) {
    for (let i = 1; i <= 3; i++) {
        document.getElementById('wizardStep' + i).classList.toggle('d-none', i !== step);
        const ind = document.getElementById('stepIndicator' + i);
        ind.classList.remove('active', 'done');
        if (i === step) ind.classList.add('active');
        else if (i < step) ind.classList.add('done');
    }
    // Back button
    document.getElementById('wizardBackBtn').classList.toggle('d-none', step === 1);
    // Next / Send buttons
    document.getElementById('wizardNextBtn').classList.toggle('d-none', step === 3);
    document.getElementById('wizardSendBtn').classList.toggle('d-none', step !== 3);

    if (step === 1) validateStep1();
    if (step === 2) validateStep2();
    if (step === 3) populateReview();
}

function goNext() {
    if (currentStep < 3) { currentStep++; showStep(currentStep); }
}

function goBack() {
    if (currentStep > 1) { currentStep--; showStep(currentStep); }
}

// ── Step validation ──────────────────────────────────
function validateStep1() {
    const anyBgy    = [...document.querySelectorAll('.barangay-checkbox')].some(c => c.checked);
    const audience  = document.querySelector('input[name="target_audience"]:checked');
    let ok = anyBgy && audience;
    if (ok && audience?.value === 'specific_purok')
        ok = !!document.getElementById('target_purok').value.trim();
    if (ok && audience?.value === 'specific_age_group')
        ok = !!document.getElementById('target_age_group').value.trim();
    const btn = document.getElementById('wizardNextBtn');
    if (btn && currentStep === 1) btn.disabled = !ok;
}

function validateStep2() {
    const type    = document.getElementById('notification_type').value;
    const title   = document.getElementById('title').value.trim();
    const message = document.getElementById('message').value.trim();
    const btn = document.getElementById('wizardNextBtn');
    if (btn && currentStep === 2) btn.disabled = !(type && title && message);
}

// ── Review population ─────────────────────────────────
function populateReview() {
    // Barangays
    const checked = [...document.querySelectorAll('.barangay-checkbox')].filter(c => c.checked);
    const bgyNames = checked.map(c => c.closest('.bgy-option')?.querySelector('.bgy-option-name')?.textContent?.trim() || c.value);
    document.getElementById('reviewBarangays').textContent = bgyNames.join(', ') || '—';

    // Audience
    const audience = document.querySelector('input[name="target_audience"]:checked');
    const audienceMap = {
        'all_residents':'All Residents','specific_purok':'Specific Purok',
        'specific_age_group':'Age Group','pregnant_women':'Pregnant Women',
        'senior_citizens':'Senior Citizens','children_0_12':'Children (0–12)'
    };
    document.getElementById('reviewAudience').textContent = audience ? (audienceMap[audience.value] || audience.value) : '—';

    // Conditional fields
    const purokVal = document.getElementById('target_purok').value.trim();
    const ageVal   = document.getElementById('target_age_group').value.trim();
    document.getElementById('reviewPurokRow').style.display = purokVal ? 'flex' : 'none';
    document.getElementById('reviewAgeRow').style.display   = ageVal   ? 'flex' : 'none';
    document.getElementById('reviewPurok').textContent = purokVal;
    document.getElementById('reviewAge').textContent   = ageVal;

    // Type
    const typeVal = document.getElementById('notification_type').value;
    const typeLabels = {
        'health_alert':'Health Alert','announcement':'Announcement','reminder':'Reminder',
        'vaccination_update':'Vaccination','clinic_schedule_update':'Clinic Schedule'
    };
    const activeTypePill = document.querySelector('.type-pill.active');
    document.getElementById('reviewType').innerHTML = activeTypePill
        ? `<span class="nbadge nbadge-${typeVal === 'health_alert' ? 'danger' : typeVal === 'announcement' ? 'primary' : typeVal === 'reminder' ? 'warning' : typeVal === 'vaccination_update' ? 'success' : 'info'}">${typeLabels[typeVal] || typeVal}</span>`
        : '—';

    // Title & Message
    document.getElementById('reviewTitle').textContent   = document.getElementById('title').value.trim() || '—';
    document.getElementById('reviewMessage').textContent = document.getElementById('message').value.trim() || '—';

    // Health alert warning
    document.getElementById('healthAlertWarning').classList.toggle('d-none', typeVal !== 'health_alert');
}

// ── Barangay dropdown ────────────────────────────────
const bgyTrigger     = document.getElementById('bgyTrigger');
const bgyPanel       = document.getElementById('bgyPanel');
const bgyTriggerText = document.getElementById('bgyTriggerText');
const bgyTags        = document.getElementById('bgyTags');
const allBgyCheckboxes = () => document.querySelectorAll('.barangay-checkbox');
const selectAllCb    = document.getElementById('selectAllBarangays');

function toggleBgyDropdown() {
    const open = bgyPanel.classList.toggle('open');
    bgyTrigger.classList.toggle('open', open);
    if (open) document.getElementById('bgySearchInput').focus();
}

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('bgyDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        bgyPanel.classList.remove('open');
        bgyTrigger.classList.remove('open');
    }
});

function filterBgy(q) {
    const lower = q.toLowerCase();
    document.querySelectorAll('#bgyOptionsList .bgy-option').forEach(opt => {
        opt.classList.toggle('hidden', !opt.dataset.name.includes(lower));
    });
}

function toggleSelectAll(cb) {
    allBgyCheckboxes().forEach(c => { c.checked = cb.checked; });
    updateBgyState();
}

function onBgyChange() {
    const all = [...allBgyCheckboxes()];
    if (selectAllCb) selectAllCb.checked = all.every(c => c.checked);
    updateBgyState();
}

function updateBgyState() {
    const checked = [...allBgyCheckboxes()].filter(c => c.checked);
    const total   = allBgyCheckboxes().length;
    if (checked.length === 0) bgyTriggerText.textContent = 'Select barangay(s)…';
    else if (checked.length === total) bgyTriggerText.textContent = 'All barangays selected';
    else bgyTriggerText.textContent = checked.length + ' barangay' + (checked.length > 1 ? 's' : '') + ' selected';

    if (bgyTags) {
        bgyTags.innerHTML = '';
        checked.forEach(cb => {
            const label = cb.closest('.bgy-option')?.querySelector('.bgy-option-name')?.textContent?.trim() || cb.value;
            const tag = document.createElement('span');
            tag.className = 'bgy-tag';
            tag.innerHTML = `<i class="bi bi-geo-alt-fill" style="font-size:.65rem"></i>${label}<button type="button" class="bgy-tag-remove" onclick="removeBgyTag('${cb.value}')"><i class="bi bi-x"></i></button>`;
            bgyTags.appendChild(tag);
        });
    }
    validateStep1();
}

function removeBgyTag(val) {
    const cb = document.querySelector(`.barangay-checkbox[value="${val}"]`);
    if (cb) { cb.checked = false; onBgyChange(); }
}

// ── Audience ─────────────────────────────────────────
function onAudienceChange(radio) {
    document.getElementById('purokField').classList.toggle('d-none',    radio.value !== 'specific_purok');
    document.getElementById('ageGroupField').classList.toggle('d-none', radio.value !== 'specific_age_group');
    document.getElementById('target_purok').required     = radio.value === 'specific_purok';
    document.getElementById('target_age_group').required = radio.value === 'specific_age_group';
    validateStep1();
}

// ── Notification type pills ──────────────────────────
function selectType(btn) {
    document.querySelectorAll('.type-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('notification_type').value = btn.dataset.value;
    validateStep2();
}

// ── Char counter ─────────────────────────────────────
function onMessageInput() {
    const len = document.getElementById('message').value.length;
    document.getElementById('charCount').textContent = len + ' / 2000';
    validateStep2();
}

// ── Form submit ───────────────────────────────────────
document.getElementById('notificationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    document.querySelector('.send-label').classList.add('d-none');
    document.querySelector('.send-loading').classList.remove('d-none');
    document.getElementById('wizardSendBtn').disabled = true;
    this.submit();
});

// ── Notification history search ───────────────────────
function filterNotifications(q) {
    const lower = q.toLowerCase();
    document.querySelectorAll('.notif-row').forEach(row => {
        row.style.display = row.dataset.search.includes(lower) ? '' : 'none';
    });
}

// ── Open detail modal ─────────────────────────────────
function openDetail(data) {
    document.getElementById('detailTypeBadge').textContent = data.type;
    document.getElementById('detailTitle').textContent     = data.title;
    document.getElementById('detailBarangays').textContent = data.targets || '—';
    document.getElementById('detailAudience').textContent  = data.audience || '—';
    document.getElementById('detailDate').textContent      = data.date || '—';
    document.getElementById('detailMessage').textContent   = data.message || '—';
    document.getElementById('detailStatus').innerHTML = `<span class="nstatus ${data.statusClass}">${data.status}</span>`;

    new bootstrap.Modal(document.getElementById('detailModal')).show();
}

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('flashToast');
    if (toast) setTimeout(() => bootstrap.Toast.getOrCreateInstance(toast).hide(), 4000);
    validateStep1();

    document.getElementById('title').addEventListener('input', validateStep2);
});
</script>
@endsection
