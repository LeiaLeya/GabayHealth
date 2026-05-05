{{--
    Reusable type-to-confirm archive modal (POST).
    Usage: @include('partials.archive-confirm-modal', ['modalId' => 'archiveModal'])
    JS API: openArchiveConfirm(id, name, action)
--}}
@php
    $modalId     = $modalId ?? 'archiveConfirmModal';
    $entityLabel = $entityLabel ?? 'Barangay';
@endphp

<div class="arc-overlay" id="{{ $modalId }}Overlay">
    <div class="arc-modal">
        <div class="arc-icon"><i class="bi bi-archive-fill"></i></div>
        <div class="arc-title" id="{{ $modalId }}Title">Archive</div>
        <div class="arc-body">
            This {{ strtolower($entityLabel) }} will be <strong>archived</strong>. All data is preserved and can be restored at any time.
            The {{ strtolower($entityLabel) }} will receive an email notification.<br><br>
            Please type <strong id="{{ $modalId }}Name"></strong> to confirm.
        </div>
        <input type="text"
               class="arc-input"
               id="{{ $modalId }}Input"
               placeholder="Type the health center name to confirm"
               oninput="arcCheckMatch('{{ $modalId }}')"
               autocomplete="off">
        <div class="arc-footer">
            <button type="button" class="arc-cancel" onclick="arcClose('{{ $modalId }}')">Cancel</button>
            <form id="{{ $modalId }}Form" method="POST">
                @csrf
                <input type="hidden" name="confirm_name" id="{{ $modalId }}Hidden">
                <button type="submit" class="arc-confirm" id="{{ $modalId }}Btn">Archive {{ $entityLabel }}</button>
            </form>
        </div>
    </div>
</div>

<style>
.arc-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.arc-overlay.open { display: flex; }
.arc-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 460px;
    padding: 28px 28px 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: arcIn .18s ease;
    margin: 16px;
}
@keyframes arcIn {
    from { opacity:0; transform:translateY(-10px); }
    to   { opacity:1; transform:translateY(0); }
}
.arc-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    background: #fef9c3;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #d97706;
    margin-bottom: 14px;
}
.arc-title { font-size: 1rem; font-weight: 700; color: #37352f; margin-bottom: 6px; }
.arc-body  { font-size: .85rem; color: #787774; line-height: 1.6; margin-bottom: 18px; }
.arc-body strong { color: #37352f; }
.arc-input {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #e9e9e7; border-radius: 7px;
    font-size: .88rem; color: #37352f; outline: none;
    transition: border-color .15s; font-family: inherit;
    margin-bottom: 18px; display: block;
    box-sizing: border-box;
}
.arc-input:focus { border-color: #94a3b8; }
.arc-input.match { border-color: #d97706; background: #fffbeb; }
.arc-footer { display: flex; gap: 10px; justify-content: flex-end; align-items: center; }
.arc-cancel {
    padding: 8px 20px; border-radius: 7px;
    border: 1px solid #e9e9e7; background: #fff;
    color: #37352f; font-size: .85rem; font-weight: 500;
    cursor: pointer; font-family: inherit; transition: background .15s;
}
.arc-cancel:hover { background: #f7f7f5; }
.arc-confirm {
    padding: 8px 20px; border-radius: 7px;
    border: none; background: #d97706;
    color: #fff; font-size: .85rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    transition: background .15s, opacity .15s;
    opacity: .38; pointer-events: none;
}
.arc-confirm.active { opacity: 1; pointer-events: auto; }
.arc-confirm.active:hover { background: #b45309; }
</style>

<script>
function openArchiveConfirm(id, name, action) {
    const mid = '{{ $modalId }}';
    document.getElementById(mid + 'Input').value = '';
    document.getElementById(mid + 'Input').classList.remove('match');
    document.getElementById(mid + 'Btn').classList.remove('active');
    document.getElementById(mid + 'Name').textContent = name;
    document.getElementById(mid + 'Title').textContent = 'Archive: ' + name;
    document.getElementById(mid + 'Form').action = action;
    document.getElementById(mid + 'Hidden').value = '';
    document.getElementById(mid + 'Overlay').classList.add('open');
    setTimeout(() => document.getElementById(mid + 'Input').focus(), 80);
}
function arcClose(mid) {
    document.getElementById(mid + 'Overlay').classList.remove('open');
}
function arcCheckMatch(mid) {
    const input    = document.getElementById(mid + 'Input');
    const expected = document.getElementById(mid + 'Name').textContent.trim().toLowerCase();
    const val      = input.value.trim().toLowerCase();
    const btn      = document.getElementById(mid + 'Btn');
    const hidden   = document.getElementById(mid + 'Hidden');
    if (val === expected) {
        btn.classList.add('active');
        input.classList.add('match');
        hidden.value = input.value;
    } else {
        btn.classList.remove('active');
        input.classList.remove('match');
        hidden.value = '';
    }
}
document.getElementById('{{ $modalId }}Overlay').addEventListener('click', function(e) {
    if (e.target === this) arcClose('{{ $modalId }}');
});
</script>
