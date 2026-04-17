@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">Rural Health Units</h1>
                <p class="text-muted mb-0">Manage and monitor all RHU accounts</p>
            </div>
        </div>

        @if(empty($ruralHealthUnits) || count($ruralHealthUnits) == 0)
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-info-circle me-2"></i>
                No Rural Health Units found.
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-building me-2"></i>All Accounts ({{ count($ruralHealthUnits) }})
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">
                                        <i class="bi bi-hospital text-muted me-2"></i>RHU Name
                                    </th>
                                    <th class="py-3">
                                        <i class="bi bi-envelope text-muted me-2"></i>Email
                                    </th>
                                    <th class="py-3 text-center">
                                        <i class="bi bi-tag text-muted me-2"></i>Status
                                    </th>
                                    <th class="py-3 text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ruralHealthUnits as $unit)
                                    <tr class="align-middle border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="fw-semibold text-dark">{{ $unit['rhuName'] ?? $unit['name'] ?? 'N/A' }}</div>
                                            @if(isset($unit['displayLocation']))
                                                <small class="text-muted d-block">{{ $unit['displayLocation'] }}</small>
                                            @elseif(isset($unit['city']))
                                                <small class="text-muted d-block">{{ $unit['city'] }}{{ isset($unit['province']) ? ', ' . $unit['province'] : '' }}</small>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <a href="mailto:{{ $unit['email'] }}" class="text-decoration-none">
                                                {{ $unit['email'] ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td class="py-3 text-center">
                                            @php
                                                $status = $unit['status'] ?? 'pending';
                                                $badgeClass = match($status) {
                                                    'approved' => 'bg-success',
                                                    'pending' => 'bg-warning text-dark',
                                                    'active' => 'bg-info',
                                                    'rejected' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                $icon = match($status) {
                                                    'approved' => 'check-circle',
                                                    'pending' => 'hourglass-split',
                                                    'active' => 'lightning-fill',
                                                    'rejected' => 'x-circle',
                                                    default => 'question-circle'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} px-3 py-2">
                                                <i class="bi bi-{{ $icon }} me-1"></i>{{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-primary send-password-btn" data-email="{{ $unit['email'] }}" data-name="{{ $unit['rhuName'] ?? $unit['name'] ?? 'RHU User' }}" title="Send password reset email">
                                                <i class="bi bi-envelope-check me-1"></i>Reset Password
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9ff;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .card {
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .bg-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .table th {
            font-weight: 600;
            color: #495057;
            border-top: none;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.send-password-btn').forEach(button => {
            button.addEventListener('click', function() {
                const email = this.getAttribute('data-email');
                const name = this.getAttribute('data-name');

                if (confirm(`Send password reset email to ${email}?`)) {
                    fetch('/admin/send-password-reset', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ email: email })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`✓ Password reset email sent to ${email}`);
                        } else {
                            alert(`Error: ${data.error || 'Failed to send email'}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while sending the email.');
                    });
                }
            });
        });
    });
    </script>
@endsection
