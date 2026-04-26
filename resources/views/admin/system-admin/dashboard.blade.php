@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a1a2e;">System Administrator Dashboard</h4>
            <p class="text-muted mb-0 small">Manage RHU applications and approvals</p>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2" style="font-size: 0.8rem;">
            <i class="bi bi-shield-check me-1"></i> System Admin
        </span>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important; border-radius: 12px;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 52px; height: 52px; background: #fef3c7;">
                        <i class="bi bi-hourglass-split" style="font-size: 1.4rem; color: #d97706;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 1.8rem; line-height: 1; color: #1a1a2e;">{{ $stats['pending'] }}</div>
                        <div class="text-muted small mt-1">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important; border-radius: 12px;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 52px; height: 52px; background: #d1fae5;">
                        <i class="bi bi-check-circle-fill" style="font-size: 1.4rem; color: #059669;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 1.8rem; line-height: 1; color: #1a1a2e;">{{ $stats['approved'] }}</div>
                        <div class="text-muted small mt-1">Approved</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important; border-radius: 12px;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 52px; height: 52px; background: #dbeafe;">
                        <i class="bi bi-hospital-fill" style="font-size: 1.4rem; color: #2563eb;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 1.8rem; line-height: 1; color: #1a1a2e;">{{ $stats['active'] }}</div>
                        <div class="text-muted small mt-1">Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important; border-radius: 12px;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 52px; height: 52px; background: #fee2e2;">
                        <i class="bi bi-x-circle-fill" style="font-size: 1.4rem; color: #dc2626;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 1.8rem; line-height: 1; color: #1a1a2e;">{{ $stats['rejected'] }}</div>
                        <div class="text-muted small mt-1">Rejected</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Applications Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header border-0 d-flex align-items-center justify-content-between py-3 px-4"
             style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-split" style="color: #d97706;"></i>
                <h6 class="mb-0 fw-semibold" style="color: #92400e;">Pending RHU Applications</h6>
            </div>
            @if(!empty($pendingRhus))
                <span class="badge" style="background:#d97706; color:#fff;">{{ count($pendingRhus) }} pending</span>
            @endif
        </div>
        <div class="card-body p-0">
            @if(empty($pendingRhus))
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem;"></i>
                    <p class="mb-0">No pending applications</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th class="ps-4 py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">RHU Name</th>
                                <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Email</th>
                                <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Location</th>
                                <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Contact</th>
                                <th class="py-3 text-muted small fw-semibold pe-4" style="border-bottom: 1px solid #e5e7eb;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRhus as $rhu)
                                <tr id="rhu-row-{{ $rhu['id'] }}" style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width: 36px; height: 36px; background: #dbeafe;">
                                                <i class="bi bi-hospital" style="color: #2563eb; font-size: 0.9rem;"></i>
                                            </div>
                                            <span class="fw-semibold" style="color: #1a1a2e;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted small">{{ $rhu['email'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="small">{{ $rhu['displayLocation'] ?? $rhu['city'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted small">{{ $rhu['phone'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 pe-4">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               style="border-radius: 8px; font-size: 0.8rem;">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-success approve-btn"
                                                    data-rhu-id="{{ $rhu['id'] }}"
                                                    style="border-radius: 8px; font-size: 0.8rem;">
                                                <i class="bi bi-check-circle me-1"></i> Approve
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #059669; border-radius: 10px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-start gap-2">
                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
                <div id="successMessage"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #dc2626; border-radius: 10px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
                <div id="errorMessage"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mt-2"
                     style="width: 56px; height: 56px; background: #d1fae5;">
                    <i class="bi bi-check-circle-fill" style="font-size: 1.8rem; color: #059669;"></i>
                </div>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <h5 class="fw-bold mb-1">Approve Application?</h5>
                <p class="text-muted small mb-0" id="approveModalMessage">
                    This will generate credentials and send them to the RHU email address.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="confirmApproveBtn" style="border-radius: 8px;">
                    <i class="bi bi-check-circle me-1"></i> Yes, Approve
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let pendingRhuId = null;
    let pendingRow = null;
    let pendingBtn = null;

    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));

    document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function () {
            pendingRhuId = this.getAttribute('data-rhu-id');
            pendingRow = document.getElementById(`rhu-row-${pendingRhuId}`);
            pendingBtn = this;

            const rhuName = pendingRow.querySelector('.fw-semibold').textContent.trim();
            document.getElementById('approveModalMessage').innerHTML =
                `Approve <strong>${rhuName}</strong> and send account setup email?`;
            approveModal.show();
        });
    });

    document.getElementById('confirmApproveBtn').addEventListener('click', function () {
        if (!pendingRhuId) return;
        const btn = pendingBtn;
        const row = pendingRow;
        const confirmBtn = this;

        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch(`/admin/system-admin/${pendingRhuId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            approveModal.hide();
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Yes, Approve';

            if (data.success) {
                document.getElementById('successMessage').innerHTML =
                    `<strong>${data.username}</strong> approved!<br>
                     <small>Setup email sent to ${data.email}</small>`;
                new bootstrap.Toast(document.getElementById('successToast'), { delay: 5000 }).show();

                setTimeout(() => {
                    row.style.transition = 'opacity 0.4s';
                    row.style.opacity = '0.4';
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Approved';
                    btn.classList.replace('btn-success', 'btn-secondary');
                }, 400);
            } else {
                document.getElementById('errorMessage').innerHTML =
                    `<strong>Error:</strong> ${data.error || 'Failed to approve RHU'}`;
                new bootstrap.Toast(document.getElementById('errorToast'), { delay: 5000 }).show();
            }
        })
        .catch(() => {
            approveModal.hide();
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Yes, Approve';
            document.getElementById('errorMessage').innerHTML =
                '<strong>Error:</strong> Request failed. Check your connection.';
            new bootstrap.Toast(document.getElementById('errorToast'), { delay: 5000 }).show();
        });
    });
});
</script>
@endsection
