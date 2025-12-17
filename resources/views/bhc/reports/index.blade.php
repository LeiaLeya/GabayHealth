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
                <select class="form-select form-select-sm" id="dateRangeFilter" style="width: 120px;">
                    <option value="week" {{ $dateRange === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $dateRange === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $dateRange === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $dateRange === 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border">
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
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-thermometer-half fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['fever_cases'] }}</h4>
                            <small class="text-muted">Fever Cases</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-bug fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['dengue_cases'] }}</h4>
                            <small class="text-muted">Dengue Cases</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
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
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-geo-alt fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-dark">{{ Str::limit($stats['top_barangay'], 15) }}</h6>
                            <small class="text-muted">Top Area ({{ $stats['top_cases'] }})</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-graph-up fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['cough_cases'] + $stats['headache_cases'] }}</h4>
                            <small class="text-muted">Other Symptoms</small>
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
                    <h5 class="card-title mb-0">
                        <i class="bi bi-map me-2"></i>Health Cases Heatmap
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="heatmap" style="height: 400px; position: relative;">
                        <div id="map" style="height: 100%; width: 100%;"></div>
                        
                        <!-- Legend -->
                        <div class="position-absolute bottom-0 start-0 m-3">
                            <div class="bg-white p-2 rounded shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="badge bg-warning rounded-circle" style="width: 12px; height: 12px;"></div>
                                    <small class="text-muted">Fever</small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="badge bg-danger rounded-circle" style="width: 12px; height: 12px;"></div>
                                    <small class="text-muted">Dengue</small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="badge bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                                    <small class="text-muted">Rash</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="badge rounded-circle" style="width: 12px; height: 12px; background-color: #1657c1;"></div>
                                    <small class="text-muted">Diarrhea</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Symptom Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="symptomChart" width="400" height="200"></canvas>
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
</style>

<!-- Include Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Initialize map
let map;
let heatmapLayer;

// Heatmap data from backend
const heatmapData = @json($heatmapData);
const chartData = @json($chartData);

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    initializeCharts();
    
    // Filter change handler
    document.getElementById('conditionFilter').addEventListener('change', function() {
        updateFilters();
    });
    
    document.getElementById('symptomFilter').addEventListener('change', function() {
        updateFilters();
    });
    
    document.getElementById('dateRangeFilter').addEventListener('change', function() {
        updateFilters();
    });
});

function initializeMap() {
    // Initialize the map centered on Minglanilla, Cebu
    map = L.map('map').setView([10.2456, 123.7890], 12);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add heatmap points with enhanced visibility
    heatmapData.forEach(point => {
        const color = getConditionColor(point.condition || 'fever');
        // Make circles much larger and more visible
        const radius = Math.max(30, point.weight * 15); // Increased base size and scaling
        
        const circle = L.circle([point.lat, point.lng], {
            color: color,
            fillColor: color,
            fillOpacity: 0.8, // Increased opacity for better visibility
            radius: radius,
            weight: 3, // Thicker border
            opacity: 0.9 // More visible border
        }).addTo(map);
        
        // Add enhanced popup with better styling
        circle.bindPopup(`
            <div style="min-width: 250px; padding: 5px;">
                <div style="font-size: 16px; font-weight: bold; color: #2563eb; margin-bottom: 8px;">
                    ${point.barangay}
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="font-weight: 600; color: #333;">Cases:</span> 
                    <span style="color: #dc3545; font-weight: bold; font-size: 18px;">${point.cases}</span>
                </div>
                <div>
                    <span style="font-weight: 600; color: #333;">Condition:</span> 
                    <span style="color: #666;">${point.condition || 'Fever'}</span>
                </div>
            </div>
        `);
    });
}

function getConditionColor(condition) {
    const colors = {
        'fever': '#ffc107',      // Bright yellow
        'dengue': '#dc3545',     // Bright red
        'diarrhea': '#1657c1',   // Blue (matching sidebar)
        'rash': '#0d6efd',       // Bright blue
        'cough': '#fd7e14',      // Orange
        'headache': '#20c997'    // Teal
    };
    return colors[condition.toLowerCase()] || '#ffc107';
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

// Chart data
const symptomData = {
    labels: ['Fever', 'Dengue', 'Diarrhea', 'Cough', 'Headache'],
    datasets: [{
        label: 'Cases',
        data: [
            {{ $stats['fever_cases'] }},
            {{ $stats['dengue_cases'] }},
            {{ $stats['diarrhea_cases'] }},
            {{ $stats['cough_cases'] }},
            {{ $stats['headache_cases'] }}
        ],
        backgroundColor: [
            '#ffc107',
            '#dc3545',
            '#1657c1',
            '#6c757d',
            '#495057'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

function initializeCharts() {
    // Symptom distribution chart
    const symptomCtx = document.getElementById('symptomChart').getContext('2d');
    new Chart(symptomCtx, {
        type: 'doughnut',
        data: symptomData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>
@endsection 