@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('rhu.barangays.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="bi bi-chevron-left me-2"></i> Back to Barangays
            </a>
            <h1 class="h2 mb-1">{{ $barangay['healthCenterName'] }}</h1>
            <p class="text-muted mb-0">{{ $barangay['barangayName'] }} - Manage credentials and account access</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Details Column -->
        <div class="col-lg-8">
            <!-- Barangay Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Barangay Health Center Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; overflow: hidden;">
                                @if($barangay['logo_url'])
                                    <img src="{{ $barangay['logo_url'] }}" 
                                         alt="{{ $barangay['healthCenterName'] }}" 
                                         style="max-height: 70px; max-width: 70px; object-fit: contain;"
                                         onerror="this.src='{{ asset('images/seal.png') }}'; this.classList.add('fallback-logo');"
                                         loading="lazy">
                                @else
                                    <img src="{{ asset('images/seal.png') }}" 
                                         alt="Seal" 
                                         class="fallback-logo"
                                         style="max-height: 70px; max-width: 70px; object-fit: contain;">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $barangay['healthCenterName'] ?? 'Health Center' }}</h4>
                            <p class="text-muted mb-1"><strong>Barangay:</strong> {{ $barangay['barangayName'] ?? 'Unknown' }}</p>
                            <p class="text-muted mb-0"><strong>Location:</strong> 
                                @php
                                    if (is_array($barangay['location'] ?? null) && isset($barangay['location']['name'])) {
                                        echo $barangay['location']['name'];
                                    } elseif (is_string($barangay['location'] ?? null)) {
                                        echo $barangay['location'];
                                    } else {
                                        echo 'Not provided';
                                    }
                                @endphp
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong>
                            <p class="text-muted mb-0">{{ $barangay['email'] ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Health Center Name:</strong>
                            <p class="text-muted mb-0">{{ $barangay['healthCenterName'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Account Status:</strong>
                            <p class="mb-0">
                                @if(($barangay['status'] ?? '') === 'active')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>
                                @elseif(($barangay['status'] ?? '') === 'pending_setup')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending Setup</span>
                                @elseif(($barangay['status'] ?? '') === 'approved')
                                    <span class="badge bg-info"><i class="bi bi-check2 me-1"></i> Approved</span>
                                @elseif(($barangay['status'] ?? '') === 'pending')
                                    <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($barangay['status'] ?? 'unknown') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Applied Date:</strong>
                            <p class="text-muted mb-0">
                                @php
                                    $createdAt = $barangay['createdAt'] ?? $barangay['created_at'] ?? $barangay['approved_at'] ?? null;
                                    if ($createdAt && is_string($createdAt)) {
                                        try {
                                            echo \Carbon\Carbon::parse($createdAt)->format('M d, Y g:i A');
                                        } catch (\Exception $e) {
                                            echo 'N/A';
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                @endphp
                            </p>
                        </div>
                    </div>

                    @if($barangay['status'] !== 'active')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Username:</strong>
                                <p class="text-muted mb-0">
                                    @if($barangay['username'] ?? false)
                                        <code>{{ $barangay['username'] }}</code>
                                    @else
                                        <em class="text-secondary">Not yet generated</em>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Address Information Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i> Address Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <strong>Full Address:</strong>
                            <p class="text-muted mb-0">
                                @php
                                    $address = $barangay['fullAddress'] ?? null;
                                    if (!$address) {
                                        if (is_array($barangay['location'] ?? null) && isset($barangay['location']['name'])) {
                                            $address = $barangay['location']['name'];
                                        } elseif (is_string($barangay['location'] ?? null)) {
                                            $address = $barangay['location'];
                                        }
                                    }
                                    echo $address ?? 'Not provided';
                                @endphp
                            </p>
                        </div>
                    </div>

                    @if(isset($barangay['region']) && $barangay['region'])
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Region:</strong>
                                <p class="text-muted mb-0">{{ $barangay['displayRegion'] ?? $barangay['region'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Province:</strong>
                                <p class="text-muted mb-0">{{ $barangay['displayProvince'] ?? $barangay['province'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>City:</strong>
                                <p class="text-muted mb-0">{{ $barangay['displayCity'] ?? $barangay['city'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Actions Card -->
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i> Account Actions</h5>
                </div>
                <div class="card-body">
                    @if(($barangay['status'] ?? '') === 'active')
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Account Active</strong>
                            <p class="mb-0 small mt-2">This barangay health center account is active and can access the system.</p>
                        </div>
                    @elseif(($barangay['status'] ?? '') === 'pending_setup')
                        <div class="alert alert-warning mb-3 text-dark">
                            <i class="bi bi-hourglass-split me-2"></i>
                            <strong>Setup in Progress</strong>
                            <p class="mb-0 small mt-2">Credentials have been sent. Waiting for password setup.</p>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Generate Credentials</strong>
                            <p class="mb-0 small mt-2">Send account setup credentials to this barangay health center.</p>
                        </div>

                        <button id="sendCredentialsBtn" class="btn btn-success w-100" onclick="sendCredentials('{{ $barangay['id'] }}')">
                            <i class="bi bi-send me-2"></i> Generate & Send Credentials
                        </button>

                        <div id="credentialsMessage" class="mt-3" style="display: none;"></div>
                    @endif

                    <hr>

                    <h6 class="mb-3">Account Information</h6>

                    @if($barangay['username'] ?? false)
                        <div class="bg-light p-3 rounded mb-3">
                            <small class="text-muted d-block">Username</small>
                            <code class="d-block">{{ $barangay['username'] }}</code>
                        </div>
                    @endif

                    @if($barangay['email'] ?? false)
                        <div class="bg-light p-3 rounded">
                            <small class="text-muted d-block">Email</small>
                            <a href="mailto:{{ $barangay['email'] }}" class="text-decoration-none">{{ $barangay['email'] }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fallback-logo {
        opacity: 0.5;
    }

    .btn-success {
        background: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background: #218838;
        border-color: #218838;
    }

    code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        color: #d63384;
    }
</style>

<script>
function sendCredentials(barangayId) {
    const btn = document.getElementById('sendCredentialsBtn');
    const messageDiv = document.getElementById('credentialsMessage');
    
    // Disable button and show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
    messageDiv.style.display = 'none';

    const sendCredentialsUrl = `{{ route('rhu.barangays.send-credentials', ['barangayId' => $barangay['id']]) }}`;

    fetch(sendCredentialsUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.error || 'Failed to send credentials');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Success!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            messageDiv.style.display = 'block';
            
            // Hide button and show info message
            btn.style.display = 'none';
            document.querySelector('.alert-info').innerHTML = `
                <i class="bi bi-hourglass-split me-2"></i>
                <strong>Setup in Progress</strong>
                <p class="mb-0 small mt-2">Credentials have been sent. Waiting for password setup.</p>
            `;
            document.querySelector('.alert-info').classList.remove('alert-info');
            document.querySelector('.alert-info').classList.add('alert-warning', 'text-dark');
            
            // Reload page after 3 seconds
            setTimeout(() => {
                location.reload();
            }, 3000);
        }
    })
    .catch(error => {
        messageDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Error!</strong> ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        messageDiv.style.display = 'block';
        
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i> Generate & Send Credentials';
    });
}
</script>
@endsection
