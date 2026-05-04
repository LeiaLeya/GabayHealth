@extends('layouts.app')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">
@endpush

@section('content')
@php
    $barangayOptions = collect($barangaysWithinRhu ?? [])->keyBy('id');
    $statusCounts = ['All' => count($events), 'Upcoming' => 0, 'Ongoing' => 0, 'Done' => 0, 'Cancelled' => 0];
    foreach ($events as $ev) { $s = $ev['status'] ?? 'Upcoming'; if (isset($statusCounts[$s])) $statusCounts[$s]++; }
    $eventsForJs = collect($events)->map(function($e) {
        return [
            'id'               => $e['id'],
            'title'            => $e['title'] ?? '',
            'date'             => $e['date'] ?? '',
            'start_time'       => $e['start_time'] ?? '',
            'end_time'         => $e['end_time'] ?? '',
            'location'         => $e['location'] ?? '',
            'latitude'         => $e['latitude'] ?? '',
            'longitude'        => $e['longitude'] ?? '',
            'description'      => $e['description'] ?? '',
            'targetAttendees'  => $e['targetAttendees'] ?? '',
            'in_charge'        => $e['in_charge'] ?? '',
            'isOpenToAll'      => $e['isOpenToAll'] ?? false,
            'allowed_barangays'=> $e['allowed_barangays'] ?? [],
            'image_url'        => $e['image_url'] ?? '',
            'status'           => $e['status'] ?? 'Upcoming',
        ];
    })->values();
@endphp

{{-- Pass event data to JS --}}
<script>
window.__events = @json($eventsForJs);
</script>

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

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Events</h2>
            <p class="text-muted mb-0 small">Manage health center events and community activities</p>
        </div>
        <button class="btn btn-add-ev" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Event
        </button>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @php
        $stats = [
            ['label'=>'Total Events',  'value'=>$statusCounts['All'],      'icon'=>'bi-calendar2-week-fill', 'bg'=>'#f1f1ef', 'color'=>'#787774'],
            ['label'=>'Upcoming',      'value'=>$statusCounts['Upcoming'], 'icon'=>'bi-calendar-check-fill', 'bg'=>'#dbeafe', 'color'=>'#1d4ed8'],
            ['label'=>'Ongoing',       'value'=>$statusCounts['Ongoing'],  'icon'=>'bi-play-circle-fill',    'bg'=>'#fef9c3', 'color'=>'#d97706'],
            ['label'=>'Done',          'value'=>$statusCounts['Done'],     'icon'=>'bi-check-circle-fill',   'bg'=>'#dcfce7', 'color'=>'#16a34a'],
        ];
        @endphp
        @foreach($stats as $stat)
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:{{ $stat['bg'] }};color:{{ $stat['color'] }};"><i class="bi {{ $stat['icon'] }}"></i></div>
                <div class="stat-info"><div class="stat-value">{{ $stat['value'] }}</div><div class="stat-label">{{ $stat['label'] }}</div></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Status Tabs --}}
    <div class="ev-tabs mb-3">
        @foreach(['All','Upcoming','Ongoing','Done','Cancelled'] as $tab)
        <button class="ev-tab {{ $tab === 'All' ? 'active' : '' }}" data-tab="{{ $tab }}" onclick="filterTab(this)">
            {{ $tab }}
            <span class="ev-tab-count">{{ $statusCounts[$tab] }}</span>
        </button>
        @endforeach
    </div>

    {{-- Events Grid --}}
    @if(count($events) > 0)
    <div class="ev-grid" id="evGrid">
        @foreach($events as $event)
        @php
            $status = $event['status'] ?? 'Upcoming';
            $statusMeta = [
                'Upcoming'  => ['class' => 'st-upcoming',  'label' => 'Upcoming'],
                'Ongoing'   => ['class' => 'st-ongoing',   'label' => 'Ongoing'],
                'Done'      => ['class' => 'st-done',      'label' => 'Done'],
                'Cancelled' => ['class' => 'st-cancelled', 'label' => 'Cancelled'],
            ][$status] ?? ['class' => 'st-upcoming', 'label' => $status];

            $allowedBarangayNames = $event['allowed_barangay_names'] ?? [];
            if (empty($allowedBarangayNames) && !empty($event['allowed_barangays'] ?? [])) {
                $allowedBarangayNames = collect($event['allowed_barangays'])
                    ->filter(fn($id) => $barangayOptions->has($id))
                    ->map(fn($id) => $barangayOptions[$id]['name'])
                    ->values()->all();
            }
        @endphp
        <div class="ev-card {{ $status === 'Cancelled' ? 'ev-card-cancelled' : '' }}" data-status="{{ $status }}">

            {{-- Banner --}}
            <div class="ev-banner" style="{{ isset($event['image_url']) && $event['image_url'] ? 'background-image:url('.e($event['image_url']).');background-size:cover;background-position:center;' : '' }}">
                @if(!isset($event['image_url']) || !$event['image_url'])
                <div class="ev-banner-placeholder"><i class="bi bi-calendar-event"></i></div>
                @endif
                <div class="ev-status-badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</div>
            </div>

            {{-- Body --}}
            <div class="ev-body">
                <h6 class="ev-title">{{ $event['title'] ?? 'Untitled' }}</h6>

                @if(!empty($event['description']))
                <p class="ev-desc">{{ Str::limit($event['description'], 80) }}</p>
                @endif

                <div class="ev-meta">
                    @if(isset($event['date']))
                    <div class="ev-meta-row">
                        <i class="bi bi-calendar3 ev-meta-icon"></i>
                        <span>{{ \Carbon\Carbon::parse($event['date'])->format('F d, Y') }}</span>
                    </div>
                    @endif

                    @if(isset($event['start_time']) && isset($event['end_time']))
                    <div class="ev-meta-row">
                        <i class="bi bi-clock ev-meta-icon"></i>
                        <span>{{ \Carbon\Carbon::parse($event['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($event['end_time'])->format('g:i A') }}</span>
                    </div>
                    @endif

                    @if(!empty($event['location']))
                    <div class="ev-meta-row">
                        <i class="bi bi-geo-alt ev-meta-icon"></i>
                        <span>{{ Str::limit($event['location'], 50) }}</span>
                    </div>
                    @endif

                    @if(!empty($event['targetAttendees']))
                    <div class="ev-meta-row">
                        <i class="bi bi-people ev-meta-icon"></i>
                        <span>{{ $event['targetAttendees'] }}</span>
                    </div>
                    @endif

                    @if(!empty($event['in_charge']))
                    <div class="ev-meta-row">
                        <i class="bi bi-person-badge ev-meta-icon"></i>
                        <span>{{ $event['in_charge'] }}</span>
                    </div>
                    @endif

                    @if(!empty($event['isOpenToAll']))
                    <div class="ev-meta-row">
                        <i class="bi bi-globe ev-meta-icon" style="color:#0891b2;"></i>
                        <span style="color:#0891b2;font-weight:500;">Open to all barangays</span>
                    </div>
                    @elseif(!empty($allowedBarangayNames))
                    <div class="ev-meta-row">
                        <i class="bi bi-geo-alt-fill ev-meta-icon" style="color:#7c3aed;"></i>
                        <span>{{ implode(', ', array_slice($allowedBarangayNames, 0, 2)) }}{{ count($allowedBarangayNames) > 2 ? ' +'.( count($allowedBarangayNames)-2).' more' : '' }}</span>
                    </div>
                    @endif

                    @if($status === 'Cancelled' && !empty($event['cancellation_reason']))
                    <div class="ev-cancel-reason">
                        <i class="bi bi-exclamation-octagon me-1"></i>{{ Str::limit($event['cancellation_reason'], 70) }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="ev-footer">
                <a href="{{ route('rhu.events.show', $event['id']) }}" class="ev-action-btn ev-btn-view">
                    <i class="bi bi-eye me-1"></i>View
                </a>
                @if($status !== 'Cancelled' && $status !== 'Done')
                <button class="ev-action-btn ev-btn-edit" onclick="openEditModal('{{ $event['id'] }}')">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
                <button class="ev-action-btn ev-btn-cancel" onclick="openCancelModal('{{ $event['id'] }}','{{ addslashes($event['title'] ?? 'this event') }}')">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="ev-empty d-none" id="evEmpty">
        <i class="bi bi-calendar-x"></i>
        <div class="ev-empty-title">No events in this category</div>
        <div class="ev-empty-text">Try a different filter tab.</div>
    </div>

    @else
    <div class="ev-empty">
        <i class="bi bi-calendar-x"></i>
        <div class="ev-empty-title">No Events Yet</div>
        <div class="ev-empty-text">Start by adding your first event using the button above.</div>
    </div>
    @endif
</div>

{{-- ─────────────────────────────────────
     Add Event Modal
───────────────────────────────────── --}}
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="ev-modal-hd">
                <div><div class="ev-modal-eyebrow">New Event</div><h5 class="ev-modal-title">Add Event</h5></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('rhu.events.store') }}" class="d-flex flex-column" enctype="multipart/form-data" id="addEventForm">
                @csrf
                <div class="modal-body p-4">
                    {{-- Title + Date --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="ev-field-label">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" required placeholder="e.g. Blood Donation Drive">
                        </div>
                        <div class="col-md-5">
                            <label class="ev-field-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control rounded-3" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                    </div>
                    {{-- Time --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="ev-field-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="add_start_time" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="ev-field-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="add_end_time" class="form-control rounded-3" required>
                            <div class="invalid-feedback">End time must be after start time.</div>
                        </div>
                    </div>
                    {{-- Location --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="ev-field-label mb-0">Location</label>
                            <button type="button" class="btn-link-sm" id="toggleLocationMode" onclick="toggleLocationInput('add')">Enter manually</button>
                        </div>
                        <div id="locationGeocoderContainer"></div>
                        <input type="text" name="location" id="location" class="form-control rounded-3" style="display:none;" placeholder="Enter address">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <small class="text-muted" style="font-size:.75rem;">Search with Mapbox or enter manually</small>
                    </div>
                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="ev-field-label">Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Describe the event…"></textarea>
                    </div>
                    {{-- Image --}}
                    <div class="mb-3">
                        <label class="ev-field-label">Event Image <span class="text-muted fw-normal">(Optional)</span></label>
                        <div class="ev-upload-zone" id="addUploadZone" onclick="document.getElementById('eventImageUpload').click()">
                            <div id="addUploadPlaceholder">
                                <i class="bi bi-image ev-upload-icon"></i>
                                <div class="ev-upload-text">Click or drag to upload</div>
                                <div class="ev-upload-hint">PNG or JPG, max 5MB</div>
                            </div>
                            <div id="addUploadPreview" class="d-none">
                                <img id="addPreviewImg" src="" alt="Preview" class="ev-preview-img">
                                <button type="button" class="ev-change-img-btn" onclick="resetAddImage(event)">Change Image</button>
                            </div>
                        </div>
                        <input type="file" id="eventImageUpload" name="image" accept="image/*" class="d-none">
                    </div>
                    {{-- Target Attendees + In Charge --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="ev-field-label">Target Attendees</label>
                            <select name="targetAttendees" class="form-select rounded-3">
                                <option value="">All residents (default)</option>
                                <option value="All Residents">All Residents</option>
                                <option value="Seniors Only">Seniors Only</option>
                                <option value="Children Only">Children Only</option>
                                <option value="Pregnant Women">Pregnant Women</option>
                                <option value="Adults Only">Adults Only</option>
                                <option value="Health Workers">Health Workers</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="ev-field-label">In Charge / Head of Event</label>
                            <input type="text" name="in_charge" class="form-control rounded-3" placeholder="Person responsible">
                        </div>
                    </div>
                    {{-- Open to All --}}
                    <div class="ev-toggle-row mb-3">
                        <label class="ev-toggle-label" for="addIsOpenToAll">
                            <i class="bi bi-globe me-2 text-info"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.88rem;">Open to All Barangays</div>
                                <div style="font-size:.76rem;color:#94a3b8;">Event is accessible to residents from any barangay</div>
                            </div>
                        </label>
                        <div class="form-check form-switch ms-auto">
                            <input class="form-check-input" type="checkbox" name="isOpenToAll" id="addIsOpenToAll" value="1" data-allowed-target="allowedBarangaysCreate" style="width:2.4em;height:1.3em;">
                        </div>
                    </div>
                    {{-- Allowed Barangays --}}
                    @if(!empty($barangaysWithinRhu))
                    <div class="mb-1" id="addBarangayDropdownWrap">
                        <label class="ev-field-label">Allow Specific Barangays</label>
                        <div class="bgy-dropdown" id="addBgyDropdown">
                            <button type="button" class="bgy-trigger" id="addBgyTrigger" onclick="toggleBgyPanel('add')">
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                <span id="addBgyTriggerText">Select barangay(s)…</span>
                                <i class="bi bi-chevron-down ms-auto bgy-chevron" id="addBgyChevron"></i>
                            </button>
                            <div class="bgy-panel" id="addBgyPanel">
                                <div class="bgy-search-wrap">
                                    <i class="bi bi-search bgy-search-icon"></i>
                                    <input type="text" class="bgy-search-input" placeholder="Search barangay…" oninput="filterBgy(this,'add')">
                                </div>
                                <div class="bgy-options-list">
                                    @foreach($barangaysWithinRhu as $barangay)
                                    <label class="bgy-option" data-name="{{ strtolower($barangay['name']) }}">
                                        <input type="checkbox" name="allowed_barangays[]" value="{{ $barangay['id'] }}" class="add-bgy-cb" onchange="updateBgyLabel('add')">
                                        <i class="bi bi-geo-alt-fill bgy-icon"></i>
                                        <span class="bgy-option-name">{{ $barangay['name'] }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="bgy-tags" id="addBgyTags"></div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top px-4 py-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4" id="addSubmitBtn">
                        <span class="add-submit-label"><i class="bi bi-check2 me-1"></i>Save Event</span>
                        <span class="add-submit-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────
     Edit Event Modal (single, populated by JS)
───────────────────────────────────── --}}
<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="ev-modal-hd">
                <div><div class="ev-modal-eyebrow">Edit Event</div><h5 class="ev-modal-title" id="editModalTitle">Edit Event</h5></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="d-flex flex-column" enctype="multipart/form-data" id="editEventForm">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="ev-field-label">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_title" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-5">
                            <label class="ev-field-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="edit_date" class="form-control rounded-3" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="ev-field-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="ev-field-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="edit_end_time" class="form-control rounded-3" required>
                            <div class="invalid-feedback">End time must be after start time.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="ev-field-label mb-0">Location</label>
                            <button type="button" class="btn-link-sm" id="toggleEditLocationMode" onclick="toggleLocationInput('edit')">Enter manually</button>
                        </div>
                        <div id="editLocationGeocoderContainer"></div>
                        <input type="text" name="location" id="edit_location" class="form-control rounded-3" style="display:none;" placeholder="Enter address">
                        <input type="hidden" name="latitude"  id="edit_latitude">
                        <input type="hidden" name="longitude" id="edit_longitude">
                    </div>
                    <div class="mb-3">
                        <label class="ev-field-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="ev-field-label">Event Image <span class="text-muted fw-normal">(Optional)</span></label>
                        <div class="ev-upload-zone" id="editUploadZone" onclick="document.getElementById('editImageUpload').click()">
                            <div id="editUploadPlaceholder">
                                <i class="bi bi-image ev-upload-icon"></i>
                                <div class="ev-upload-text">Click or drag to upload</div>
                                <div class="ev-upload-hint">PNG or JPG, max 5MB</div>
                            </div>
                            <div id="editUploadPreview" class="d-none">
                                <img id="editPreviewImg" src="" alt="Preview" class="ev-preview-img">
                                <button type="button" class="ev-change-img-btn" onclick="resetEditImage(event)">Change Image</button>
                            </div>
                        </div>
                        <input type="file" id="editImageUpload" name="image" accept="image/*" class="d-none">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="ev-field-label">Target Attendees</label>
                            <select name="targetAttendees" id="edit_targetAttendees" class="form-select rounded-3">
                                <option value="">All residents (default)</option>
                                <option value="All Residents">All Residents</option>
                                <option value="Seniors Only">Seniors Only</option>
                                <option value="Children Only">Children Only</option>
                                <option value="Pregnant Women">Pregnant Women</option>
                                <option value="Adults Only">Adults Only</option>
                                <option value="Health Workers">Health Workers</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="ev-field-label">In Charge / Head of Event</label>
                            <input type="text" name="in_charge" id="edit_in_charge" class="form-control rounded-3" placeholder="Person responsible">
                        </div>
                    </div>
                    <div class="ev-toggle-row mb-3">
                        <label class="ev-toggle-label" for="editIsOpenToAll">
                            <i class="bi bi-globe me-2 text-info"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.88rem;">Open to All Barangays</div>
                                <div style="font-size:.76rem;color:#94a3b8;">Event is accessible to residents from any barangay</div>
                            </div>
                        </label>
                        <div class="form-check form-switch ms-auto">
                            <input class="form-check-input" type="checkbox" name="isOpenToAll" id="editIsOpenToAll" value="1" data-allowed-target="editAllowedBarangays" style="width:2.4em;height:1.3em;">
                        </div>
                    </div>
                    @if(!empty($barangaysWithinRhu))
                    <div class="mb-1" id="editBarangayDropdownWrap">
                        <label class="ev-field-label">Allow Specific Barangays</label>
                        <div class="bgy-dropdown" id="editBgyDropdown">
                            <button type="button" class="bgy-trigger" id="editBgyTrigger" onclick="toggleBgyPanel('edit')">
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                <span id="editBgyTriggerText">Select barangay(s)…</span>
                                <i class="bi bi-chevron-down ms-auto bgy-chevron" id="editBgyChevron"></i>
                            </button>
                            <div class="bgy-panel" id="editBgyPanel">
                                <div class="bgy-search-wrap">
                                    <i class="bi bi-search bgy-search-icon"></i>
                                    <input type="text" class="bgy-search-input" placeholder="Search barangay…" oninput="filterBgy(this,'edit')">
                                </div>
                                <div class="bgy-options-list">
                                    @foreach($barangaysWithinRhu as $barangay)
                                    <label class="bgy-option" data-name="{{ strtolower($barangay['name']) }}">
                                        <input type="checkbox" name="allowed_barangays[]" value="{{ $barangay['id'] }}" class="edit-bgy-cb" onchange="updateBgyLabel('edit')">
                                        <i class="bi bi-geo-alt-fill bgy-icon"></i>
                                        <span class="bgy-option-name">{{ $barangay['name'] }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="bgy-tags" id="editBgyTags"></div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top px-4 py-3 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4" id="editSubmitBtn">
                        <span class="edit-submit-label"><i class="bi bi-check2 me-1"></i>Update Event</span>
                        <span class="edit-submit-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Cancel Event Modal ─── --}}
<div class="modal fade" id="cancelEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="ev-modal-hd" style="background:#dc2626;">
                <div><div class="ev-modal-eyebrow">Confirm</div><h5 class="ev-modal-title">Cancel Event</h5></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="cancelEventForm">
                @csrf
                <div class="modal-body p-4">
                    <p class="mb-3">Please provide a reason for cancelling <strong id="cancelEventTitle"></strong>.</p>
                    <label class="ev-field-label">Cancellation Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control rounded-3" rows="3" required maxlength="500" placeholder="Enter reason…"></textarea>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Go Back</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-x-circle me-1"></i>Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
*, *::before, *::after { box-sizing: border-box; }

.btn-add-ev { background:#1657c1; border:none; color:#fff; font-size:.82rem; font-weight:500; padding:6px 16px; border-radius:6px; transition:opacity .1s; }
.btn-add-ev:hover { opacity:.82; color:#fff; }

.stat-card { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:6px; background:#fff; border:1px solid #e9e9e7; }
.stat-icon { width:36px; height:36px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.stat-value { font-size:1.5rem; font-weight:700; color:#37352f; line-height:1; }
.stat-label { font-size:.72rem; color:#9b9b9b; font-weight:400; margin-top:2px; }

/* ── Tabs ── */
.ev-tabs { display:flex; gap:4px; border-bottom:1px solid #e9e9e7; }
.ev-tab { background:none; border:none; padding:8px 14px 10px; font-size:.84rem; font-weight:500; color:#787774; border-bottom:2px solid transparent; margin-bottom:-1px; cursor:pointer; transition:all .15s; border-radius:4px 4px 0 0; display:flex; align-items:center; gap:6px; }
.ev-tab:hover { color:#37352f; background:#f7f7f5; }
.ev-tab.active { color:#37352f; border-bottom-color:#37352f; }
.ev-tab-count { font-size:.66rem; font-weight:700; background:#f1f1ef; color:#787774; padding:1px 7px; border-radius:10px; }
.ev-tab.active .ev-tab-count { background:#37352f; color:#fff; }

/* ── Grid ── */
.ev-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px; }

/* ── Card ── */
.ev-card { background:#fff; border:1px solid #e9e9e7; border-radius:10px; overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .15s; }
.ev-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
.ev-card-cancelled { opacity:.75; }
.ev-card-cancelled .ev-title { text-decoration:line-through; color:#9b9b9b; }

.ev-banner { height:140px; background:#f1f5f9; position:relative; display:flex; align-items:center; justify-content:center; }
.ev-banner-placeholder { display:flex; align-items:center; justify-content:center; width:100%; height:100%; }
.ev-banner-placeholder i { font-size:2.5rem; color:#cbd5e1; }
.ev-status-badge { position:absolute; top:10px; left:10px; font-size:.66rem; font-weight:700; letter-spacing:.4px; text-transform:uppercase; padding:3px 9px; border-radius:20px; }
.st-upcoming  { background:#dbeafe; color:#1d4ed8; }
.st-ongoing   { background:#fef9c3; color:#d97706; }
.st-done      { background:#dcfce7; color:#16a34a; }
.st-cancelled { background:#f1f5f9; color:#64748b; }

.ev-body { padding:14px 16px; flex:1; }
.ev-title { font-size:.9rem; font-weight:700; color:#1e293b; margin-bottom:6px; line-height:1.3; }
.ev-desc { font-size:.77rem; color:#6b7280; margin-bottom:8px; line-height:1.4; }
.ev-meta { display:flex; flex-direction:column; gap:4px; }
.ev-meta-row { display:flex; align-items:flex-start; gap:7px; font-size:.78rem; color:#475569; }
.ev-meta-icon { font-size:.8rem; color:#94a3b8; flex-shrink:0; margin-top:1px; }
.ev-cancel-reason { font-size:.75rem; color:#dc2626; background:#fef2f2; border:1px solid #fecaca; border-radius:5px; padding:5px 9px; margin-top:8px; }

.ev-footer { display:flex; gap:6px; padding:12px 16px; border-top:1px solid #f1f5f9; }
.ev-action-btn { display:inline-flex; align-items:center; font-size:.76rem; font-weight:500; padding:5px 11px; border-radius:6px; border:1px solid #e2e8f0; background:#fff; color:#475569; cursor:pointer; text-decoration:none; transition:all .12s; }
.ev-action-btn:hover { background:#f8fafc; color:#1e293b; }
.ev-btn-view:hover  { border-color:#1657c1; color:#1657c1; }
.ev-btn-edit:hover  { border-color:#1657c1; background:#eff6ff; color:#1657c1; }
.ev-btn-cancel:hover { border-color:#dc2626; background:#fef2f2; color:#dc2626; }

/* ── Empty ── */
.ev-empty { text-align:center; padding:60px 20px; color:#94a3b8; background:#fff; border:1px solid #e9e9e7; border-radius:8px; }
.ev-empty i { font-size:2.5rem; display:block; margin-bottom:12px; }
.ev-empty-title { font-size:.95rem; font-weight:600; color:#374151; margin-bottom:4px; }
.ev-empty-text { font-size:.82rem; }

/* ── Modal chrome ── */
.ev-modal-hd { display:flex; align-items:flex-start; justify-content:space-between; background:#1657c1; padding:18px 22px; }
.ev-modal-eyebrow { font-size:.64rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:5px; display:inline-block; }
.ev-modal-title { font-size:1rem; font-weight:600; color:#fff; margin:0; }
.ev-field-label { font-size:.8rem; font-weight:600; color:#374151; display:block; margin-bottom:5px; }
.btn-link-sm { background:none; border:none; color:#1657c1; font-size:.76rem; cursor:pointer; padding:0; }
.btn-link-sm:hover { text-decoration:underline; }

/* ── Upload zone ── */
.ev-upload-zone { border:2px dashed #e2e8f0; border-radius:8px; background:#fafafa; cursor:pointer; min-height:90px; display:flex; align-items:center; justify-content:center; overflow:hidden; transition:all .15s; text-align:center; padding:16px; }
.ev-upload-zone:hover { border-color:#1657c1; background:#f8fafc; }
.ev-upload-icon { font-size:1.8rem; color:#cbd5e1; display:block; margin-bottom:6px; }
.ev-upload-text { font-size:.83rem; font-weight:500; color:#475569; }
.ev-upload-hint { font-size:.73rem; color:#94a3b8; margin-top:2px; }
.ev-preview-img { max-width:100%; max-height:180px; border-radius:6px; object-fit:contain; display:block; margin-bottom:8px; }
.ev-change-img-btn { background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; padding:4px 12px; font-size:.76rem; cursor:pointer; color:#374151; }
.ev-change-img-btn:hover { background:#e2e8f0; }

/* ── Open-to-all toggle row ── */
.ev-toggle-row { display:flex; align-items:center; gap:12px; padding:12px 14px; background:#fafaf9; border:1px solid #f1f1ef; border-radius:8px; }
.ev-toggle-label { display:flex; align-items:center; gap:8px; cursor:pointer; flex:1; }

/* ── Barangay dropdown ── */
.bgy-dropdown { position:relative; }
.bgy-trigger { width:100%; display:flex; align-items:center; gap:6px; padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; font-size:.85rem; color:#374151; cursor:pointer; text-align:left; transition:border-color .15s; }
.bgy-trigger:hover, .bgy-trigger.open { border-color:#1657c1; }
.bgy-chevron { font-size:.7rem; transition:transform .2s; color:#94a3b8; }
.bgy-trigger.open .bgy-chevron { transform:rotate(180deg); }
.bgy-panel { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.1); display:none; flex-direction:column; max-height:240px; overflow:hidden; }
.bgy-panel.open { display:flex; }
.bgy-search-wrap { position:relative; padding:8px 8px 4px; flex-shrink:0; }
.bgy-search-icon { position:absolute; left:16px; top:50%; transform:translateY(-30%); color:#94a3b8; font-size:.78rem; pointer-events:none; }
.bgy-search-input { width:100%; padding:5px 10px 5px 28px; border:1px solid #e2e8f0; border-radius:6px; font-size:.82rem; outline:none; }
.bgy-search-input:focus { border-color:#1657c1; }
.bgy-options-list { overflow-y:auto; flex:1; padding:4px 8px 8px; }
.bgy-option { display:flex; align-items:center; gap:8px; padding:6px 6px; cursor:pointer; border-radius:5px; font-size:.83rem; color:#374151; transition:background .1s; }
.bgy-option:hover { background:#f8fafc; }
.bgy-option.bgy-hidden { display:none; }
.bgy-option input[type="checkbox"] { accent-color:#1657c1; flex-shrink:0; cursor:pointer; }
.bgy-icon { color:#1657c1; font-size:.76rem; flex-shrink:0; }
.bgy-tags { display:flex; flex-wrap:wrap; gap:5px; margin-top:7px; }
.bgy-tag { display:inline-flex; align-items:center; gap:4px; background:#eff6ff; color:#1657c1; border:1px solid #bfdbfe; font-size:.73rem; font-weight:500; padding:2px 8px 2px 6px; border-radius:20px; }
.bgy-tag-remove { background:none; border:none; color:#93c5fd; padding:0; cursor:pointer; font-size:.78rem; line-height:1; }
.bgy-tag-remove:hover { color:#1657c1; }

.form-control, .form-select { border-color:#e2e8f0; font-size:.88rem; }
.form-control:focus, .form-select:focus { border-color:#1657c1; box-shadow:0 0 0 3px rgba(22,87,193,.12); }

.mapboxgl-ctrl-geocoder { width:100%; max-width:100%; border-radius:8px !important; }
.mapboxgl-ctrl-geocoder input { padding:8px 12px !important; }
.mapboxgl-ctrl-geocoder .suggestions { z-index:1050 !important; position:absolute !important; }
#editLocationGeocoderContainer .mapboxgl-ctrl-geocoder .suggestions { display:none !important; }
#editLocationGeocoderContainer .mapboxgl-ctrl-geocoder .suggestions.active { display:block !important; }
</style>

<script>
// ── Status tab filter ────────────────────────────────
function filterTab(btn) {
    document.querySelectorAll('.ev-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const tab = btn.dataset.tab;
    const cards = document.querySelectorAll('.ev-card');
    let visible = 0;
    cards.forEach(card => {
        const show = tab === 'All' || card.dataset.status === tab;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const empty = document.getElementById('evEmpty');
    if (empty) empty.classList.toggle('d-none', visible > 0);
}

// ── Open Add Modal ────────────────────────────────────
function openAddModal() {
    new bootstrap.Modal(document.getElementById('addEventModal')).show();
}

// ── Open Edit Modal ───────────────────────────────────
function openEditModal(id) {
    const ev = (window.__events || []).find(e => e.id === id || e.id == id);
    if (!ev) return;

    document.getElementById('editModalTitle').textContent = ev.title;
    document.getElementById('editEventForm').action = `/rhu/events/${id}`;

    document.getElementById('edit_title').value           = ev.title || '';
    document.getElementById('edit_date').value            = ev.date || '';
    document.getElementById('edit_start_time').value      = ev.start_time || '';
    document.getElementById('edit_end_time').value        = ev.end_time || '';
    document.getElementById('edit_description').value     = ev.description || '';
    document.getElementById('edit_location').value        = ev.location || '';
    document.getElementById('edit_latitude').value        = ev.latitude || '';
    document.getElementById('edit_longitude').value       = ev.longitude || '';
    document.getElementById('edit_in_charge').value       = ev.in_charge || '';
    document.getElementById('edit_targetAttendees').value = ev.targetAttendees || '';
    document.getElementById('editIsOpenToAll').checked    = !!ev.isOpenToAll;

    // Image
    const placeholder = document.getElementById('editUploadPlaceholder');
    const preview     = document.getElementById('editUploadPreview');
    const previewImg  = document.getElementById('editPreviewImg');
    if (ev.image_url) {
        previewImg.src = ev.image_url;
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
    } else {
        placeholder.classList.remove('d-none');
        preview.classList.add('d-none');
        previewImg.src = '';
    }

    // Barangay checkboxes
    document.querySelectorAll('.edit-bgy-cb').forEach(cb => {
        cb.checked = (ev.allowed_barangays || []).includes(cb.value);
    });
    updateBgyLabel('edit');

    // Reset submit button
    document.querySelector('.edit-submit-label').classList.remove('d-none');
    document.querySelector('.edit-submit-loading').classList.add('d-none');
    document.getElementById('editSubmitBtn').disabled = false;

    new bootstrap.Modal(document.getElementById('editEventModal')).show();
}

// ── Open Cancel Modal ─────────────────────────────────
function openCancelModal(id, title) {
    document.getElementById('cancelEventTitle').textContent = title;
    document.getElementById('cancelEventForm').action = `/rhu/events/${id}/cancel`;
    new bootstrap.Modal(document.getElementById('cancelEventModal')).show();
}

// ── Barangay dropdown ─────────────────────────────────
function toggleBgyPanel(prefix) {
    const panel   = document.getElementById(prefix + 'BgyPanel');
    const trigger = document.getElementById(prefix + 'BgyTrigger');
    const open    = panel.classList.toggle('open');
    trigger.classList.toggle('open', open);
}

document.addEventListener('click', function(e) {
    ['add','edit'].forEach(prefix => {
        const dropdown = document.getElementById(prefix + 'BgyDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            document.getElementById(prefix + 'BgyPanel')?.classList.remove('open');
            document.getElementById(prefix + 'BgyTrigger')?.classList.remove('open');
        }
    });
});

function filterBgy(input, prefix) {
    const q = input.value.toLowerCase();
    input.closest('.bgy-panel').querySelectorAll('.bgy-option').forEach(opt => {
        opt.classList.toggle('bgy-hidden', !opt.dataset.name.includes(q));
    });
}

function updateBgyLabel(prefix) {
    const checked = [...document.querySelectorAll(`.${prefix}-bgy-cb`)].filter(c => c.checked);
    const triggerText = document.getElementById(prefix + 'BgyTriggerText');
    const tags = document.getElementById(prefix + 'BgyTags');

    if (triggerText) {
        triggerText.textContent = checked.length
            ? checked.length + ' barangay' + (checked.length > 1 ? 's' : '') + ' selected'
            : 'Select barangay(s)…';
    }
    if (tags) {
        tags.innerHTML = '';
        checked.forEach(cb => {
            const name = cb.closest('.bgy-option')?.querySelector('.bgy-option-name')?.textContent?.trim() || cb.value;
            const tag = document.createElement('span');
            tag.className = 'bgy-tag';
            tag.innerHTML = `<i class="bi bi-geo-alt-fill" style="font-size:.62rem"></i>${name}<button type="button" class="bgy-tag-remove" onclick="removeBgyTag('${prefix}','${cb.value}')"><i class="bi bi-x"></i></button>`;
            tags.appendChild(tag);
        });
    }
}

function removeBgyTag(prefix, val) {
    const cb = document.querySelector(`.${prefix}-bgy-cb[value="${val}"]`);
    if (cb) { cb.checked = false; updateBgyLabel(prefix); }
}

// ── Image upload ──────────────────────────────────────
function setupImageUpload(zoneId, inputId, placeholderId, previewId, previewImgId) {
    const zone        = document.getElementById(zoneId);
    const input       = document.getElementById(inputId);
    const placeholder = document.getElementById(placeholderId);
    const preview     = document.getElementById(previewId);
    const previewImg  = document.getElementById(previewImgId);
    if (!zone || !input) return;

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        if (!file.type.match('image.*')) { alert('Please select an image file.'); return; }
        if (file.size > 5 * 1024 * 1024) { alert('File size must be less than 5MB.'); return; }
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });

    ['dragover','dragleave','drop'].forEach(evt => zone.addEventListener(evt, e => {
        e.preventDefault();
        if (evt === 'dragover') zone.style.borderColor = '#1657c1';
        if (evt === 'dragleave') zone.style.borderColor = '';
        if (evt === 'drop') {
            zone.style.borderColor = '';
            const file = e.dataTransfer.files[0];
            if (file) { const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files; input.dispatchEvent(new Event('change', {bubbles:true})); }
        }
    }));
}

function resetAddImage(e) {
    e.stopPropagation();
    document.getElementById('eventImageUpload').value = '';
    document.getElementById('addUploadPlaceholder').classList.remove('d-none');
    document.getElementById('addUploadPreview').classList.add('d-none');
}

function resetEditImage(e) {
    e.stopPropagation();
    document.getElementById('editImageUpload').value = '';
    document.getElementById('editUploadPlaceholder').classList.remove('d-none');
    document.getElementById('editUploadPreview').classList.add('d-none');
}

// ── Time validation ───────────────────────────────────
function setupTimeValidation(startId, endId) {
    const start = document.getElementById(startId);
    const end   = document.getElementById(endId);
    if (!start || !end) return;
    const validate = () => {
        if (start.value && end.value) {
            const invalid = start.value >= end.value;
            end.setCustomValidity(invalid ? 'End time must be after start time.' : '');
            end.classList.toggle('is-invalid', invalid);
        }
    };
    start.addEventListener('change', validate);
    end.addEventListener('change', validate);
}

// ── Submit loading states ─────────────────────────────
document.getElementById('addEventForm')?.addEventListener('submit', function() {
    document.querySelector('.add-submit-label').classList.add('d-none');
    document.querySelector('.add-submit-loading').classList.remove('d-none');
    document.getElementById('addSubmitBtn').disabled = true;
});
document.getElementById('editEventForm')?.addEventListener('submit', function() {
    document.querySelector('.edit-submit-label').classList.add('d-none');
    document.querySelector('.edit-submit-loading').classList.remove('d-none');
    document.getElementById('editSubmitBtn').disabled = true;
});

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    setupImageUpload('addUploadZone',  'eventImageUpload', 'addUploadPlaceholder',  'addUploadPreview',  'addPreviewImg');
    setupImageUpload('editUploadZone', 'editImageUpload',  'editUploadPlaceholder', 'editUploadPreview', 'editPreviewImg');
    setupTimeValidation('add_start_time', 'add_end_time');
    setupTimeValidation('edit_start_time', 'edit_end_time');
    updateBgyLabel('add');
    updateBgyLabel('edit');

    // Reset add modal on close
    document.getElementById('addEventModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('addEventForm').reset();
        document.getElementById('addUploadPlaceholder').classList.remove('d-none');
        document.getElementById('addUploadPreview').classList.add('d-none');
        document.getElementById('addPreviewImg').src = '';
        updateBgyLabel('add');
        const lbl = document.querySelector('.add-submit-label');
        const spn = document.querySelector('.add-submit-loading');
        if (lbl) lbl.classList.remove('d-none');
        if (spn) spn.classList.add('d-none');
        document.getElementById('addSubmitBtn').disabled = false;
    });

    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000);
    });
});
</script>

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
<script>
const mapboxToken = @json(config('services.mapbox.access_token'));
if (mapboxToken) {
    mapboxgl.accessToken = mapboxToken;
    let addGeocoder = null, editGeocoder = null;

    window.toggleLocationInput = function(mode) {
        const isAdd = mode === 'add';
        const cont  = document.getElementById(isAdd ? 'locationGeocoderContainer' : 'editLocationGeocoderContainer');
        const inp   = document.getElementById(isAdd ? 'location' : 'edit_location');
        const btn   = document.getElementById(isAdd ? 'toggleLocationMode' : 'toggleEditLocationMode');
        if (!cont || !inp || !btn) return;
        const manualVisible = inp.style.display !== 'none';
        if (manualVisible) {
            inp.style.display = 'none'; cont.style.display = 'block';
            btn.textContent = 'Enter manually';
            if (isAdd && !addGeocoder) initAddGeocoder();
            else if (!isAdd && !editGeocoder) initEditGeocoder();
        } else {
            cont.style.display = 'none'; inp.style.display = 'block';
            btn.textContent = 'Use map search';
            document.getElementById(isAdd ? 'latitude' : 'edit_latitude').value = '';
            document.getElementById(isAdd ? 'longitude' : 'edit_longitude').value = '';
        }
    };

    function initAddGeocoder() {
        const cont = document.getElementById('locationGeocoderContainer');
        if (!cont) return;
        cont.innerHTML = '';
        addGeocoder = new MapboxGeocoder({ accessToken: mapboxToken, mapboxgl, placeholder:'Search for a location…', countries:'ph', proximity:[123.8854,10.3157], types:'address,poi,place,locality,neighborhood', marker:false, minLength:2, limit:10 });
        addGeocoder.addTo(cont);
        addGeocoder.on('result', e => {
            document.getElementById('location').value  = e.result.place_name;
            document.getElementById('latitude').value  = e.result.geometry.coordinates[1];
            document.getElementById('longitude').value = e.result.geometry.coordinates[0];
        });
        addGeocoder.on('clear', () => {
            document.getElementById('location').value = document.getElementById('latitude').value = document.getElementById('longitude').value = '';
        });
    }

    function initEditGeocoder() {
        const cont = document.getElementById('editLocationGeocoderContainer');
        if (!cont || editGeocoder) return;
        cont.innerHTML = '';
        editGeocoder = new MapboxGeocoder({ accessToken: mapboxToken, mapboxgl, placeholder:'Search for a location…', countries:'ph', proximity:[123.8854,10.3157], types:'address,poi,place,locality,neighborhood', marker:false, minLength:2, limit:10 });
        editGeocoder.addTo(cont);
        const loc = document.getElementById('edit_location').value;
        if (loc) {
            setTimeout(() => {
                const inp = cont.querySelector('input[type="text"]');
                if (inp) { inp.value = loc; setTimeout(() => { const s = cont.querySelector('.suggestions'); if(s){s.style.display='none';s.classList.remove('active');} inp.blur(); }, 150); }
            }, 200);
        }
        const lat = document.getElementById('edit_latitude').value;
        if (!lat) { cont.style.display = 'none'; document.getElementById('edit_location').style.display = 'block'; document.getElementById('toggleEditLocationMode').textContent = 'Use map search'; }
        editGeocoder.on('result', e => {
            document.getElementById('edit_location').value  = e.result.place_name;
            document.getElementById('edit_latitude').value  = e.result.geometry.coordinates[1];
            document.getElementById('edit_longitude').value = e.result.geometry.coordinates[0];
        });
        editGeocoder.on('clear', () => {
            document.getElementById('edit_location').value = document.getElementById('edit_latitude').value = document.getElementById('edit_longitude').value = '';
        });
    }

    document.getElementById('addEventModal')?.addEventListener('shown.bs.modal', initAddGeocoder);
    document.getElementById('addEventModal')?.addEventListener('hidden.bs.modal', () => { addGeocoder = null; const c = document.getElementById('locationGeocoderContainer'); if(c) c.innerHTML=''; });
    document.getElementById('editEventModal')?.addEventListener('shown.bs.modal', initEditGeocoder);
    document.getElementById('editEventModal')?.addEventListener('hidden.bs.modal', () => { editGeocoder = null; });
}
</script>
@endpush
@endsection
