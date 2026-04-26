@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="mb-4">
        <a href="{{ route('admin.system-admin.dashboard') }}"
           class="d-inline-flex align-items-center gap-1 text-muted small text-decoration-none mb-2"
           style="transition: color 0.2s;"
           onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color=''">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <h4 class="fw-bold mb-1" style="color: #1a1a2e;">Approved RHUs — Credentials Sent</h4>
        <p class="text-muted mb-0 small">RHUs waiting to activate their accounts</p>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th class="ps-4 py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">RHU Name</th>
                        <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Username</th>
                        <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Email</th>
                        <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Approved</th>
                        <th class="py-3 text-muted small fw-semibold" style="border-bottom: 1px solid #e5e7eb;">Status</th>
                        <th class="py-3 text-muted small fw-semibold pe-4" style="border-bottom: 1px solid #e5e7eb;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRhus as $rhu)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    @if($rhu['logo_url'] ?? false)
                                        <img src="{{ $rhu['logo_url'] }}" alt="Logo"
                                             class="rounded-circle flex-shrink-0"
                                             width="36" height="36" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 36px; height: 36px; background: #dbeafe;">
                                            <i class="bi bi-hospital" style="color: #2563eb; font-size: 0.9rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold" style="color: #1a1a2e;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                                        <div class="text-muted small">{{ $rhu['city'] ?? 'N/A' }}{{ isset($rhu['province']) && $rhu['province'] ? ', ' . $rhu['province'] : '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <code class="px-2 py-1 rounded small" style="background:#f1f5f9; color:#1a1a2e;">{{ $rhu['username'] ?? 'N/A' }}</code>
                            </td>
                            <td class="py-3">
                                <a href="mailto:{{ $rhu['email'] }}" class="text-muted small text-decoration-none">{{ $rhu['email'] }}</a>
                            </td>
                            <td class="py-3">
                                <span class="text-muted small">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y h:i A') }}</span>
                            </td>
                            <td class="py-3">
                                <span class="badge fw-medium px-2 py-1"
                                      style="background:#d1fae5; color:#065f46; border-radius: 6px; font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i> Awaiting Activation
                                </span>
                            </td>
                            <td class="py-3 pe-4">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View details"
                                       style="border-radius: 8px; font-size: 0.8rem;">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info resend-btn"
                                            data-rhu-id="{{ $rhu['id'] }}"
                                            title="Resend credentials"
                                            style="border-radius: 8px; font-size: 0.8rem;">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem;"></i>
                                No RHUs with credentials sent
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #059669; border-radius: 10px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span>Credentials resent successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #dc2626; border-radius: 10px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="errorMsg">Failed to resend credentials.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Resend Confirmation Modal -->
<div class="modal fade" id="resendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mt-2"
                     style="width: 56px; height: 56px; background: #dbeafe;">
                    <i class="bi bi-arrow-repeat" style="font-size: 1.8rem; color: #2563eb;"></i>
                </div>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <h5 class="fw-bold mb-1">Resend Credentials?</h5>
                <p class="text-muted small mb-0">The login credentials will be re-sent to the RHU's registered email address.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmResendBtn" style="border-radius: 8px;">
                    <i class="bi bi-arrow-repeat me-1"></i> Yes, Resend
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let pendingId = null;

    const resendModal = new bootstrap.Modal(document.getElementById('resendModal'));

    document.querySelectorAll('.resend-btn').forEach(button => {
        button.addEventListener('click', function () {
            pendingId = this.getAttribute('data-rhu-id');
            resendModal.show();
        });
    });

    document.getElementById('confirmResendBtn').addEventListener('click', function () {
        if (!pendingId) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        fetch(`/admin/system-admin/${pendingId}/resend-credentials`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            resendModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Yes, Resend';

            if (data.success) {
                new bootstrap.Toast(document.getElementById('successToast'), { delay: 4000 }).show();
            } else {
                document.getElementById('errorMsg').textContent = data.error || 'Failed to resend credentials.';
                new bootstrap.Toast(document.getElementById('errorToast'), { delay: 4000 }).show();
            }
        })
        .catch(() => {
            resendModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Yes, Resend';
            document.getElementById('errorMsg').textContent = 'Request failed. Check your connection.';
            new bootstrap.Toast(document.getElementById('errorToast'), { delay: 4000 }).show();
        });
    });
});
</script>
@endsection
