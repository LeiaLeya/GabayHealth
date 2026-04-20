@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Community Health Heatmap</h2>
            <p class="text-muted mb-0">Health surveillance and monitoring dashboard</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('reports.verify') }}" class="btn btn-warning">
                <i class="bi bi-patch-check me-2"></i>Verify Reports
            </a>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-semibold">Filter by:</label>
                <select class="form-select form-select-sm" id="conditionFilter" style="width: 150px;">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Reports</option>
                    <option value="fever" {{ $filter === 'fever' ? 'selected' : '' }}>Fever</option>
                    <option value="dengue" {{ $filter === 'dengue' ? 'selected' : '' }}>Dengue</option>
                    <option value="diarrhea" {{ $filter === 'diarrhea' ? 'selected' : '' }}>Diarrhea</option>
                    <option value="rash" {{ $filter === 'rash' ? 'selected' : '' }}>Rash</option>
                </select>
                <select class="form-select form-select-sm" id="symptomFilter" style="width: 150px;">
                    <option value="all" {{ $symptomFilter === 'all' ? 'selected' : '' }}>All Symptoms</option>
                    @foreach($availableSymptoms as $symptom)
                        <option value="{{ strtolower($symptom) }}" {{ $symptomFilter === strtolower($symptom) ? 'selected' : '' }}>{{ $symptom }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @php
        $priorityCases = ($stats['fever_cases'] ?? 0) + ($stats['dengue_cases'] ?? 0);
    @endphp

    <input type="hidden" id="dateRangeFilter" value="{{ $dateRange ?? 'month' }}">

    <!-- KPI + Controls -->
    <div class="row mb-4 g-3">
        <div class="col-lg-7 h-100">
            <div class="row g-3 h-100">
                <div class="col-md-4">
                    <div class="card border kpi-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-clipboard-pulse fs-2 text-dark"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0 text-dark">{{ $stats['total_cases'] }}</h4>
                                    <small class="text-muted">Total Cases</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border kpi-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-exclamation-triangle fs-2 text-dark"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0 text-dark">{{ $priorityCases }}</h4>
                                    <small class="text-muted">Priority (Fever + Dengue)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border kpi-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-activity fs-2 text-dark"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0 text-dark">{{ $stats['recent_cases'] }}</h4>
                                    <small class="text-muted">Recent (7 days)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border controls-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-sliders me-2"></i>Layer and Time Controls</h5>
                    <small class="text-white-50">Early warning visibility</small>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="toggleUnverified" checked>
                                <label class="form-check-label" for="toggleUnverified">Show unverified symptoms</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="toggleConfirmedOnly">
                                <label class="form-check-label" for="toggleConfirmedOnly">Show confirmed cases only</label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="toggleHotspots" checked>
                                <label class="form-check-label" for="toggleHotspots">Show hotspots / risk radius</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="timeWindowSlider" class="form-label fw-semibold mb-1">
                                Time window: <span id="timeWindowValue" class="text-primary">This Month</span>
                            </label>
                            <input type="range" class="form-range" id="timeWindowSlider" min="1" max="7" step="1" value="3">
                            <div class="d-flex justify-content-between small text-muted">
                                <span>7d</span><span>14d</span><span>30d</span><span>60d</span><span>90d</span><span>180d</span><span>365d</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Heatmap Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0">
                            Verified Disease Bubble Map
                        </h5>
                        <small class="text-white-50">Verified (solid) + possible (faded) layers</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="heatmap" class="map-wrapper">
                        <div id="map" style="height: 100%; width: 100%;"></div>
                        
                        <!-- Legend -->
                        <div class="position-absolute bottom-0 start-0 m-3">
                            <div class="bg-white p-2 rounded shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="badge bg-danger rounded-circle" style="width: 12px; height: 12px;"></div>
                                    <small class="text-muted">Dengue Category (Verified)</small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="badge bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                                    <small class="text-muted">Respiratory Category (Verified)</small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="badge bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                                    <small class="text-muted">Waterborne Category (Verified)</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="badge rounded-circle" style="width: 12px; height: 12px; background-color: rgba(220,53,69,0.45); border: 1px solid #dc3545;"></div>
                                    <small class="text-muted">Faded = Unverified / Possible cases</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.card {
    border-radius: 1rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, #1657c1 0%, #0d6efd 100%);
    color: white;
    border-bottom: none;
    border-radius: 1rem 1rem 0 0 !important;
}

.form-select-sm {
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.form-select-sm:focus {
    border-color: #1657c1;
    box-shadow: 0 0 0 0.2rem rgba(22, 87, 193, 0.25);
}

.btn {
    border-radius: 0.5rem;
    font-weight: 500;
}

.kpi-card .card-body {
    padding: 0.9rem 1rem 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    position: relative;
}

.kpi-card .card-body .d-flex {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    width: 100%;
    height: 100%;
}

.kpi-card .card-body .flex-grow-1 {
    margin-left: 0 !important;
}

.kpi-card .card-body .flex-shrink-0 {
    position: absolute;
    top: 0.7rem;
    right: 0.8rem;
}

.kpi-card {
    aspect-ratio: auto;
    min-height: 145px;
}

.kpi-card h4 {
    font-size: clamp(2rem, 2.3vw, 2.4rem);
    font-weight: 700;
    line-height: 1;
}

.kpi-card small {
    font-size: 1rem;
    font-weight: 500;
}

.kpi-card .bi {
    font-size: 1.35rem !important;
    opacity: 0.75;
}

.controls-card .card-body {
    min-height: 105px;
    padding: 0.65rem 0.9rem;
}

.controls-card .form-check {
    margin-bottom: 0.35rem !important;
}

.controls-card .form-check-label,
.controls-card .form-label,
.controls-card .small {
    font-size: 0.82rem;
}

.map-wrapper {
    position: relative;
    height: clamp(420px, calc(100vh - 320px), 980px);
}

.container-fluid {
    overflow-x: hidden;
}

@media (max-width: 991.98px) {
    .map-wrapper {
        height: clamp(360px, calc(100vh - 250px), 760px);
    }
}

.bg-purple {
    background-color: #1657c1 !important;
}

.heatmap-legend {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: white;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.heatmap-point {
    border-radius: 50%;
    opacity: 0.7;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.bubble-marker {
    background: transparent;
    border: none;
    text-align: center;
}

.bubble-content {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: 4px solid white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    transition: transform 0.2s;
}

.bubble-content:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 12px rgba(0,0,0,0.4);
}

.form-range::-webkit-slider-thumb {
    background: #0d6efd;
}

.form-range::-moz-range-thumb {
    background: #0d6efd;
}
</style>

<!-- Include Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map
let map;
let heatmapLayer;

// Map data from backend
const verifiedBubbleData = @json($verifiedBubbleData ?? []);
const unverifiedBubbleData = @json($unverifiedBubbleData ?? []);
const hotspotData = @json($hotspotData ?? []);
const chartData = @json($chartData);
const centerLat = {{ $centerLat ?? 10.2456 }};
const centerLng = {{ $centerLng ?? 123.7890 }};
const verifiedLayers = [];
const unverifiedLayers = [];
const hotspotLayers = [];
const riskRadiusLayers = [];
const timeWindowDays = [7, 14, 30, 60, 90, 180, 365];

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    
    document.getElementById('conditionFilter')?.addEventListener('change', updateFilters);
    document.getElementById('symptomFilter')?.addEventListener('change', updateFilters);

    const toggleUnverified = document.getElementById('toggleUnverified');
    const toggleConfirmedOnly = document.getElementById('toggleConfirmedOnly');
    const toggleHotspots = document.getElementById('toggleHotspots');
    loadToggleStates();
    syncToggleDependencies();
    if (toggleUnverified) {
        toggleUnverified.addEventListener('change', function() {
            saveToggleStates();
            renderLayerVisibility();
        });
    }
    if (toggleConfirmedOnly) {
        toggleConfirmedOnly.addEventListener('change', function() {
            syncToggleDependencies();
            saveToggleStates();
            renderLayerVisibility();
        });
    }
    if (toggleHotspots) {
        toggleHotspots.addEventListener('change', function() {
            saveToggleStates();
            renderLayerVisibility();
        });
    }

    setupTimeWindowSlider();
});

function initializeMap() {
    // Initialize the map centered on the current barangay (fallback to Minglanilla, Cebu)
    map = L.map('map').setView([centerLat, centerLng], 13);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Custom panes to keep hotspots behind bubbles while preserving clickability.
    map.createPane('hotspotPane');
    map.getPane('hotspotPane').style.zIndex = 410;
    map.createPane('unverifiedPane');
    map.getPane('unverifiedPane').style.zIndex = 470;
    map.createPane('verifiedPane');
    map.getPane('verifiedPane').style.zIndex = 490;

    hotspotData.forEach(zone => {
        const color = getCategoryColor(zone.diseaseCategory);
        const layer = L.circle([zone.lat, zone.lng], {
            pane: 'hotspotPane',
            radius: zone.radius,
            color: color,
            fillColor: color,
            fillOpacity: 0.12,
            opacity: 0.4,
            weight: 2
        }).addTo(map);

        layer.bindPopup(`
            <div style="min-width: 240px; padding: 6px;">
                <div class="fw-bold mb-1">Hotspot Zone</div>
                <div><span class="text-muted">Disease:</span> <strong>${formatCategory(zone.diseaseCategory)}</strong></div>
                <div><span class="text-muted">Barangays involved:</span> <strong>${zone.barangayCount}</strong></div>
                <div><span class="text-muted">Total confirmed cases:</span> <strong>${zone.totalCases}</strong></div>
            </div>
        `);
        hotspotLayers.push(layer);
    });
    
    const verifiedCoordinateTotals = countPointsByCoordinate(verifiedBubbleData);
    const verifiedCoordinateIndex = new Map();

    verifiedBubbleData.forEach(point => {
        const color = getCategoryColor(point.diseaseCategory);
        const radius = getBubbleRadius(point.totalCases, 140, 18);
        const coordinateKey = getCoordinateKey(point.lat, point.lng);
        const totalAtCoordinate = verifiedCoordinateTotals.get(coordinateKey) || 1;
        const indexAtCoordinate = verifiedCoordinateIndex.get(coordinateKey) || 0;
        verifiedCoordinateIndex.set(coordinateKey, indexAtCoordinate + 1);
        const [markerLat, markerLng] = offsetCoordinate(
            point.lat,
            point.lng,
            indexAtCoordinate,
            totalAtCoordinate
        );

        const verified = L.circleMarker([markerLat, markerLng], {
            pane: 'verifiedPane',
            radius: radius,
            color: color,
            fillColor: color,
            fillOpacity: 0.9,
            weight: 2
        }).addTo(map);

        verified.bindTooltip(`${point.barangay}: ${point.totalCases} verified`, { direction: 'top' });
        verified.bindPopup(`
            <div style="min-width: 260px; padding: 6px;">
                <div class="fw-bold mb-1">${point.barangay}</div>
                <div><span class="text-muted">Confirmed disease category:</span> <strong>${formatCategory(point.diseaseCategory)}</strong></div>
                <div><span class="text-muted">Confirmed cases:</span> <strong>${point.totalCases}</strong></div>
                <div><span class="text-muted">Total confirmed in barangay:</span> <strong>${point.barangayTotalCases ?? point.totalCases}</strong></div>
            </div>
        `);
        verifiedLayers.push(verified);

        if ((point.totalCases || 0) >= 8) {
            const radiusLayer = L.circle([point.lat, point.lng], {
                pane: 'hotspotPane',
                radius: 250 + (point.totalCases * 25),
                color: color,
                fillColor: color,
                fillOpacity: 0.08,
                opacity: 0.3,
                weight: 1
            }).addTo(map);
            riskRadiusLayers.push(radiusLayer);
        }
    });

    unverifiedBubbleData.forEach(point => {
        const color = getCategoryColor(point.possibleCategory);
        const radius = getBubbleRadius(point.totalSignals, 110, 15);
        const layer = L.circleMarker([point.lat, point.lng], {
            pane: 'unverifiedPane',
            radius: radius,
            color: color,
            fillColor: color,
            fillOpacity: 0.3,
            opacity: 0.6,
            weight: 1,
            dashArray: '3,3'
        }).addTo(map);

        layer.bindTooltip(`${point.barangay}: ${point.totalSignals} possible`, { direction: 'bottom' });
        layer.bindPopup(`
            <div style="min-width: 260px; padding: 6px;">
                <div class="fw-bold mb-1">${point.barangay}</div>
                <div><span class="text-muted">Possible category:</span> <strong>${formatCategory(point.possibleCategory)}</strong></div>
                <div><span class="text-muted">Unverified symptom reports:</span> <strong>${point.totalSignals}</strong></div>
                <small class="text-warning">Unverified / Early warning signal only</small>
            </div>
        `);
        unverifiedLayers.push(layer);
    });

    renderLayerVisibility();
}

function getCategoryColor(category) {
    const colors = {
        dengue: '#dc3545',
        respiratory: '#0d6efd',
        waterborne: '#198754'
    };
    return colors[category] || '#6c757d';
}

function formatCategory(category) {
    const labels = {
        dengue: 'Dengue',
        respiratory: 'Respiratory Disease',
        waterborne: 'Waterborne Disease'
    };
    return labels[category] || 'Other';
}

function getCoordinateKey(lat, lng) {
    return `${Number(lat).toFixed(6)},${Number(lng).toFixed(6)}`;
}

function countPointsByCoordinate(points) {
    const counts = new Map();
    points.forEach((point) => {
        const key = getCoordinateKey(point.lat, point.lng);
        counts.set(key, (counts.get(key) || 0) + 1);
    });
    return counts;
}

function offsetCoordinate(lat, lng, index, total) {
    const baseLat = Number(lat);
    const baseLng = Number(lng);
    if (!Number.isFinite(baseLat) || !Number.isFinite(baseLng) || total <= 1) {
        return [baseLat, baseLng];
    }

    // Small radial offset so same-location categories remain visible.
    const offsetStep = 0.00045;
    const angle = (2 * Math.PI * index) / total;
    return [
        baseLat + (Math.sin(angle) * offsetStep),
        baseLng + (Math.cos(angle) * offsetStep),
    ];
}

function getBubbleRadius(count, maxRadius = 140, scale = 16) {
    const safe = Math.max(1, Number(count || 0));
    return Math.min(maxRadius, Math.max(10, Math.sqrt(safe) * scale));
}

function renderLayerVisibility() {
    const showUnverified = document.getElementById('toggleUnverified')?.checked ?? true;
    const confirmedOnly = document.getElementById('toggleConfirmedOnly')?.checked ?? false;
    const showHotspots = document.getElementById('toggleHotspots')?.checked ?? true;
    const showUnverifiedLayer = showUnverified && !confirmedOnly;
    const showHotspotLayer = showHotspots && !confirmedOnly;

    unverifiedLayers.forEach((layer) => {
        if (showUnverifiedLayer && !map.hasLayer(layer)) map.addLayer(layer);
        if (!showUnverifiedLayer && map.hasLayer(layer)) map.removeLayer(layer);
    });

    hotspotLayers.forEach((layer) => {
        if (showHotspotLayer && !map.hasLayer(layer)) map.addLayer(layer);
        if (!showHotspotLayer && map.hasLayer(layer)) map.removeLayer(layer);
    });

    riskRadiusLayers.forEach((layer) => {
        if (showHotspotLayer && !map.hasLayer(layer)) map.addLayer(layer);
        if (!showHotspotLayer && map.hasLayer(layer)) map.removeLayer(layer);
    });
}

function syncToggleDependencies() {
    const confirmedOnly = document.getElementById('toggleConfirmedOnly')?.checked ?? false;
    const unverifiedToggle = document.getElementById('toggleUnverified');
    const hotspotsToggle = document.getElementById('toggleHotspots');

    if (unverifiedToggle) {
        unverifiedToggle.disabled = confirmedOnly;
    }
    if (hotspotsToggle) {
        hotspotsToggle.disabled = confirmedOnly;
    }
}

function setupTimeWindowSlider() {
    const slider = document.getElementById('timeWindowSlider');
    const label = document.getElementById('timeWindowValue');
    if (!slider || !label) {
        return;
    }

    const currentDateRange = document.getElementById('dateRangeFilter')?.value || 'month';
    const initialIndexMap = { week: 1, month: 3, quarter: 5, year: 7 };
    slider.value = initialIndexMap[currentDateRange] || 3;
    updateTimeWindowLabel(Number(slider.value), label);

    slider.addEventListener('input', function() {
        updateTimeWindowLabel(Number(this.value), label);
    });

    slider.addEventListener('change', function() {
        const days = timeWindowDays[Number(this.value) - 1] || 30;
        const mappedRange = mapDaysToDateRange(days);
        const dateRangeFilter = document.getElementById('dateRangeFilter');
        if (dateRangeFilter) {
            dateRangeFilter.value = mappedRange;
        }
        updateFilters();
    });
}

function saveToggleStates() {
    try {
        const payload = {
            showUnverified: document.getElementById('toggleUnverified')?.checked ?? true,
            confirmedOnly: document.getElementById('toggleConfirmedOnly')?.checked ?? false,
            showHotspots: document.getElementById('toggleHotspots')?.checked ?? true,
        };
        localStorage.setItem('gabayHealth.reportMapToggles', JSON.stringify(payload));
    } catch (e) {
        // Ignore localStorage failures.
    }
}

function loadToggleStates() {
    try {
        const raw = localStorage.getItem('gabayHealth.reportMapToggles');
        if (!raw) return;
        const state = JSON.parse(raw);
        if (typeof state.showUnverified === 'boolean' && document.getElementById('toggleUnverified')) {
            document.getElementById('toggleUnverified').checked = state.showUnverified;
        }
        if (typeof state.confirmedOnly === 'boolean' && document.getElementById('toggleConfirmedOnly')) {
            document.getElementById('toggleConfirmedOnly').checked = state.confirmedOnly;
        }
        if (typeof state.showHotspots === 'boolean' && document.getElementById('toggleHotspots')) {
            document.getElementById('toggleHotspots').checked = state.showHotspots;
        }
    } catch (e) {
        // Ignore parse failures.
    }
}

function updateTimeWindowLabel(index, labelEl) {
    const days = timeWindowDays[index - 1] || 30;
    if (days < 30) {
        labelEl.textContent = `${days} days`;
        return;
    }
    if (days === 30) {
        labelEl.textContent = 'This Month';
        return;
    }
    if (days === 90) {
        labelEl.textContent = 'This Quarter';
        return;
    }
    if (days === 365) {
        labelEl.textContent = 'This Year';
        return;
    }
    labelEl.textContent = `${days} days`;
}

function mapDaysToDateRange(days) {
    if (days <= 14) return 'week';
    if (days <= 60) return 'month';
    if (days <= 120) return 'quarter';
    return 'year';
}

function updateFilters() {
    const conditionFilter = document.getElementById('conditionFilter').value;
    const symptomFilter = document.getElementById('symptomFilter').value;
    const dateRangeFilter = document.getElementById('dateRangeFilter').value;
    
    const url = new URL(window.location);
    url.searchParams.set('filter', conditionFilter);
    url.searchParams.set('symptom', symptomFilter);
    url.searchParams.set('date_range', dateRangeFilter);
    
    window.location.href = url.toString();
}

</script>
@endsection 