@extends('layouts.app')

@section('content')
<style>
    /* ── Notion-style tokens ── */
    :root {
        --notion-bg:       #f7f8fc;
        --notion-surface:  #ffffff;
        --notion-border:   #e3e8f0;
        --notion-text:     #1e293b;
        --notion-muted:    #64748b;
        --notion-blue:     #1657c1;
        --notion-blue-lt:  #eff4ff;
        --notion-blue-md:  #dbeafe;
        --notion-blue-dk:  #1e40af;
    }

    .n-page-title   { font-size: 1.65rem; font-weight: 700; color: var(--notion-text); letter-spacing: -0.3px; }
    .n-page-sub     { font-size: 0.825rem; color: var(--notion-muted); margin-top: 2px; }

    /* stat strip */
    .n-stat-strip   { display: flex; gap: 1px; background: var(--notion-border); border: 1px solid var(--notion-border);
                       border-radius: 10px; overflow: hidden; }
    .n-stat-cell    { flex: 1; background: var(--notion-surface); padding: 18px 22px; }
    .n-stat-cell:first-child { border-radius: 10px 0 0 10px; }
    .n-stat-cell:last-child  { border-radius: 0 10px 10px 0; }
    .n-stat-label   { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
                       color: var(--notion-muted); margin-bottom: 6px; }
    .n-stat-value   { font-size: 2rem; font-weight: 700; line-height: 1; color: var(--notion-text); }
    .n-stat-dot     { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px;
                       position: relative; top: -1px; }

    /* section block */
    .n-block        { background: var(--notion-surface); border: 1px solid var(--notion-border);
                       border-radius: 10px; overflow: hidden; }
    .n-block-header { display: flex; align-items: center; justify-content: space-between;
                       padding: 14px 20px; border-bottom: 1px solid var(--notion-border); }
    .n-block-title  { font-size: 0.8rem; font-weight: 600; text-transform: uppercase;
                       letter-spacing: 0.07em; color: var(--notion-muted); display: flex; align-items: center; gap: 8px; }
    .n-block-title i { color: var(--notion-blue); font-size: 0.95rem; }

    /* inline link button */
    .n-link-btn     { font-size: 0.775rem; color: var(--notion-blue); text-decoration: none; font-weight: 500;
                       padding: 4px 10px; border-radius: 6px; border: 1px solid var(--notion-border);
                       background: var(--notion-surface); display: inline-flex; align-items: center; gap: 4px;
                       transition: background 0.15s, border-color 0.15s; }
    .n-link-btn:hover { background: var(--notion-blue-lt); border-color: var(--notion-blue); color: var(--notion-blue); }

    /* pending row list */
    .n-row          { display: flex; align-items: center; padding: 13px 20px; border-bottom: 1px solid #f1f5f9;
                       transition: background 0.12s; gap: 0; }
    .n-row:last-child { border-bottom: none; }
    .n-row:hover    { background: var(--notion-blue-lt); }
    .n-row-avatar   { width: 34px; height: 34px; border-radius: 8px; background: var(--notion-blue-md);
                       display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                       margin-right: 12px; }
    .n-row-avatar i { color: var(--notion-blue); font-size: 1rem; }
    .n-row-name     { font-size: 0.875rem; font-weight: 600; color: var(--notion-text); white-space: nowrap;
                       overflow: hidden; text-overflow: ellipsis; max-width: 220px; }
    .n-row-meta     { font-size: 0.775rem; color: var(--notion-muted); margin-top: 1px; }
    .n-col-email    { flex: 1; min-width: 0; padding: 0 16px; }
    .n-col-email span { font-size: 0.8rem; color: var(--notion-muted); white-space: nowrap; overflow: hidden;
                         text-overflow: ellipsis; display: block; }
    .n-col-date     { width: 110px; font-size: 0.775rem; color: var(--notion-muted); flex-shrink: 0; }
    .n-col-actions  { flex-shrink: 0; display: flex; gap: 6px; margin-left: 12px; }

    /* action buttons */
    .n-btn-view     { font-size: 0.775rem; padding: 5px 12px; border-radius: 6px; border: 1px solid var(--notion-border);
                       background: var(--notion-surface); color: var(--notion-blue); font-weight: 500;
                       transition: background 0.15s, border-color 0.15s; cursor: pointer; }
    .n-btn-view:hover { background: var(--notion-blue-md); border-color: var(--notion-blue); }
    .n-btn-approve  { font-size: 0.775rem; padding: 5px 12px; border-radius: 6px; border: 1px solid var(--notion-blue);
                       background: var(--notion-blue); color: #fff; font-weight: 500;
                       transition: background 0.15s; cursor: pointer; }
    .n-btn-approve:hover { background: var(--notion-blue-dk); border-color: var(--notion-blue-dk); }
    .n-btn-approve:disabled { opacity: 0.55; cursor: not-allowed; }

    /* column label row */
    .n-col-labels   { display: flex; align-items: center; padding: 8px 20px;
                       border-bottom: 1px solid var(--notion-border); background: #fafbfd; }
    .n-col-lbl      { font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
                       letter-spacing: 0.06em; color: #94a3b8; }

    /* empty state */
    .n-empty        { padding: 48px 20px; text-align: center; color: var(--notion-muted); }
    .n-empty i      { font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1; }
    .n-empty p      { font-size: 0.875rem; margin: 0; }

    /* badge pill */
    .n-badge        { font-size: 0.7rem; font-weight: 600; padding: 3px 9px; border-radius: 20px;
                       display: inline-flex; align-items: center; gap: 4px; }
    .n-badge-amber  { background: #fef3c7; color: #92400e; }
    .n-badge-blue   { background: var(--notion-blue-md); color: var(--notion-blue-dk); }
</style>

<div class="container-fluid" style="max-width: 1100px;">

    <!-- ── Page Header ─────────────────────────────────── -->
    <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
            <h1 class="n-page-title mb-0">System Administrator</h1>
            <p class="n-page-sub">Overview of RHU registrations and account management</p>
        </div>
        <span class="n-badge n-badge-blue mt-1">
            <i class="bi bi-shield-check"></i> Admin
        </span>
    </div>

    <!-- ── Stat Strip ───────────────────────────────────── -->
    <div class="n-stat-strip mb-4">
        <div class="n-stat-cell">
            <div class="n-stat-label">
                <span class="n-stat-dot" style="background:#f59e0b;"></span>Pending
            </div>
            <div class="n-stat-value">{{ $stats['pending'] }}</div>
        </div>
        <div class="n-stat-cell">
            <div class="n-stat-label">
                <span class="n-stat-dot" style="background:#10b981;"></span>Credentials Sent
            </div>
            <div class="n-stat-value">{{ $stats['credentials_sent'] ?? 0 }}</div>
        </div>
        <div class="n-stat-cell">
            <div class="n-stat-label">
                <span class="n-stat-dot" style="background:#1657c1;"></span>Active
            </div>
            <div class="n-stat-value">{{ $stats['active'] }}</div>
        </div>
    </div>

    <!-- ── Pending RHU Applications ─────────────────────── -->
    <div class="n-block mb-4">
        <div class="n-block-header">
            <span class="n-block-title">
                <i class="bi bi-hourglass-split"></i>
                Pending RHU Applications
                @if(!empty($pendingRhus))
                    <span class="n-badge n-badge-amber ms-1">{{ count($pendingRhus) }}</span>
                @endif
            </span>
            <a href="{{ route('admin.system-admin.all-rhus') }}" class="n-link-btn">
                View all RHUs <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if(empty($pendingRhus))
            <div class="n-empty">
                <i class="bi bi-inbox"></i>
                <p>No pending applications — you're all caught up.</p>
            </div>
        @else
            <!-- Column labels -->
            <div class="n-col-labels">
                <div style="width:34px; margin-right:12px;"></div>
                <div style="min-width:180px; max-width:220px; margin-right:0;" class="n-col-lbl">RHU Name</div>
                <div class="flex-1 n-col-lbl" style="flex:1; padding:0 16px;">Email</div>
                <div style="width:110px;" class="n-col-lbl">Applied</div>
                <div style="width:130px; margin-left:12px;" class="n-col-lbl">Actions</div>
            </div>

            @foreach($pendingRhus as $rhu)
                <div class="n-row" id="rhu-row-{{ $rhu['id'] }}">
                    <div class="n-row-avatar">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div style="min-width:180px; max-width:220px;">
                        <div class="n-row-name">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                        <div class="n-row-meta">{{ $rhu['displayLocation'] ?? $rhu['city'] ?? '' }}</div>
                    </div>
                    <div class="n-col-email">
                        <span>{{ $rhu['email'] ?? 'N/A' }}</span>
                    </div>
                    <div class="n-col-date">
                        {{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') }}
                    </div>
                    <div class="n-col-actions">
                        <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}"
                           class="n-btn-view">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                        <button type="button"
                                class="n-btn-approve approve-btn"
                                data-rhu-id="{{ $rhu['id'] }}">
                            <i class="bi bi-check me-1"></i>Approve
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</div>

<!-- ── Toast ─────────────────────────────────────────── -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #1657c1; border-radius: 10px; min-width: 300px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-start gap-2">
                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
                <div id="successMessage"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #dc2626; border-radius: 10px; min-width: 300px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
                <div id="errorMessage"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- ── Approve Modal ──────────────────────────────────── -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0" style="border-radius: 12px; border: 1px solid #e3e8f0;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px; height:44px; border-radius:10px; background:#eff4ff;
                                display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="bi bi-check-circle-fill" style="font-size:1.4rem; color:#1657c1;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#1e293b;">Approve Application?</div>
                        <div class="small" style="color:#64748b;" id="approveModalMessage">
                            Credentials will be generated and sent to the RHU's email.
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="n-btn-view" data-bs-dismiss="modal"
                            style="padding:7px 16px; font-size:0.825rem;">Cancel</button>
                    <button type="button" class="n-btn-approve" id="confirmApproveBtn"
                            style="padding:7px 16px; font-size:0.825rem;">
                        <i class="bi bi-check me-1"></i> Yes, Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let pendingRhuId = null;
    let pendingRow   = null;
    let pendingBtn   = null;

    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));

    document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function () {
            pendingRhuId = this.getAttribute('data-rhu-id');
            pendingRow   = document.getElementById(`rhu-row-${pendingRhuId}`);
            pendingBtn   = this;

            const rhuName = pendingRow.querySelector('.n-row-name').textContent.trim();
            document.getElementById('approveModalMessage').innerHTML =
                `Approve <strong>${rhuName}</strong> and send account setup email?`;
            approveModal.show();
        });
    });

    document.getElementById('confirmApproveBtn').addEventListener('click', function () {
        if (!pendingRhuId) return;
        const btn        = pendingBtn;
        const row        = pendingRow;
        const confirmBtn = this;

        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

        fetch(`/admin/system-admin/${pendingRhuId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(r => r.json())
        .then(data => {
            approveModal.hide();
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-check me-1"></i> Yes, Approve';

            if (data.success) {
                document.getElementById('successMessage').innerHTML =
                    `<strong>${data.username}</strong> approved!<br>
                     <small>Setup email sent to ${data.email}</small>`;
                new bootstrap.Toast(document.getElementById('successToast'), { delay: 5000 }).show();

                setTimeout(() => {
                    row.style.transition = 'opacity 0.4s';
                    row.style.opacity    = '0.35';
                    btn.disabled         = true;
                    btn.innerHTML        = '<i class="bi bi-check-circle-fill me-1"></i>Approved';
                    btn.style.background = '#64748b';
                    btn.style.borderColor = '#64748b';
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
            confirmBtn.innerHTML = '<i class="bi bi-check me-1"></i> Yes, Approve';
            document.getElementById('errorMessage').innerHTML =
                '<strong>Error:</strong> Request failed. Check your connection.';
            new bootstrap.Toast(document.getElementById('errorToast'), { delay: 5000 }).show();
        });
    });
});
</script>
@endsection
