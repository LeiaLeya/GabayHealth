{{--
    Reusable type-to-confirm delete modal.
    Usage: @include('partials.delete-confirm-modal', ['modalId' => 'deleteModal'])
    JS API: openDeleteConfirm(id, name, formAction)
--}}
@php $modalId = $modalId ?? 'deleteConfirmModal'; @endphp

<div class="del-overlay" id="{{ $modalId }}Overlay">
    <div class="del-modal">
        <div class="del-icon"><i class="bi bi-trash3-fill"></i></div>
        <div class="del-title" id="{{ $modalId }}Title">Delete</div>
        <div class="del-body">
            This action <strong>cannot be undone</strong>. Please type
            <strong id="{{ $modalId }}Name"></strong> to confirm.
        </div>
        <input type="text"
               class="del-input"
               id="{{ $modalId }}Input"
               placeholder="Type the name to confirm"
               oninput="delCheckMatch('{{ $modalId }}')"
               autocomplete="off">
        <div class="del-footer">
            <button type="button" class="del-cancel" onclick="delClose('{{ $modalId }}')">Cancel</button>
            <form id="{{ $modalId }}Form" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm_title" id="{{ $modalId }}Hidden">
                <button type="submit" class="del-confirm" id="{{ $modalId }}Btn">Delete</button>
            </form>
        </div>
    </div>
</div>

<style>
.del-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.del-overlay.open { display: flex; }
.del-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 440px;
    padding: 28px 28px 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: delIn .18s ease;
    margin: 16px;
}
@keyframes delIn {
    from { opacity:0; transform:translateY(-10px); }
    to   { opacity:1; transform:translateY(0); }
}
.del-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    background: #fee2e2;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #dc2626;
    margin-bottom: 14px;
}
.del-title { font-size: 1rem; font-weight: 700; color: #37352f; margin-bottom: 6px; }
.del-body  { font-size: .85rem; color: #787774; line-height: 1.6; margin-bottom: 18px; }
.del-body strong { color: #37352f; }
.del-input {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #e9e9e7; border-radius: 7px;
    font-size: .88rem; color: #37352f; outline: none;
    transition: border-color .15s; font-family: inherit;
    margin-bottom: 18px; display: block;
}
.del-input:focus { border-color: #94a3b8; }
.del-input.match { border-color: #dc2626; background: #fff5f5; }
.del-footer { display: flex; gap: 10px; justify-content: flex-end; align-items: center; }
.del-cancel {
    padding: 8px 20px; border-radius: 7px;
    border: 1px solid #e9e9e7; background: #fff;
    color: #37352f; font-size: .85rem; font-weight: 500;
    cursor: pointer; font-family: inherit; transition: background .15s;
}
.del-cancel:hover { background: #f7f7f5; }
.del-confirm {
    padding: 8px 20px; border-radius: 7px;
    border: none; background: #dc2626;
    color: #fff; font-size: .85rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    transition: background .15s, opacity .15s;
    opacity: .38; pointer-events: none;
}
.del-confirm.active { opacity: 1; pointer-events: auto; }
.del-confirm.active:hover { background: #b91c1c; }
</style>

<script>
function openDeleteConfirm(id, name, action, titleLabel) {
    const mid = '{{ $modalId }}';
    document.getElementById(mid + 'Input').value = '';
    document.getElementById(mid + 'Input').classList.remove('match');
    document.getElementById(mid + 'Btn').classList.remove('active');
    document.getElementById(mid + 'Name').textContent = name;
    document.getElementById(mid + 'Title').textContent = (titleLabel || 'Delete') + ': ' + name;
    document.getElementById(mid + 'Form').action = action;
    document.getElementById(mid + 'Hidden').value = '';
    document.getElementById(mid + 'Overlay').classList.add('open');
    setTimeout(() => document.getElementById(mid + 'Input').focus(), 80);
}
function delClose(mid) {
    document.getElementById(mid + 'Overlay').classList.remove('open');
}
function delCheckMatch(mid) {
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
    if (e.target === this) delClose('{{ $modalId }}');
});
</script>
