{{-- Cropper.js for Facebook-style profile image cropping --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<!-- Image Crop Modal -->
<div class="modal fade" id="imageCropModal" tabindex="-1" aria-labelledby="imageCropModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageCropModalLabel">
                    <i class="bi bi-crop me-2"></i>Crop Profile Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="cropModalClose"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Drag the crop box or image to reposition, scroll to zoom. The circular area will be your profile photo.</p>
                <div class="row">
                    <div class="col-md-8">
                        <div class="img-container" style="max-height: 400px; background: #f0f0f0;">
                            <img id="cropImage" src="" alt="Crop preview" style="max-width: 100%; max-height: 400px;">
                        </div>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span class="small text-muted">Zoom:</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomOut" title="Zoom out"><i class="bi bi-dash-lg"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomIn" title="Zoom in"><i class="bi bi-plus-lg"></i></button>
                            <span class="small text-muted ms-1">• Drag crop box or image • Scroll to zoom • Double-click to toggle</span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                        <p class="small text-muted mb-2">Preview</p>
                        <div id="circlePreview" class="rounded-circle overflow-hidden bg-light" style="width: 150px; height: 150px; border: 2px solid #dee2e6;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cropCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropApplyBtn">
                    <i class="bi bi-check-lg me-1"></i>Apply
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let cropper = null;
    let currentForm = null;
    let currentFileInput = null;

    // Handle all personnel image inputs
    document.querySelectorAll('.personnel-image-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) {
                if (file) alert('Please select an image file (JPEG, PNG, etc.)');
                return;
            }

            currentFileInput = input;
            currentForm = input.closest('form');

            const reader = new FileReader();
            reader.onload = function(event) {
                const cropModal = document.getElementById('imageCropModal');
                const cropImage = document.getElementById('cropImage');

                // Destroy previous cropper if exists
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }

                cropImage.src = event.target.result;
                const bsModal = new bootstrap.Modal(cropModal);
                bsModal.show();

                cropModal.addEventListener('shown.bs.modal', function initCropper() {
                    cropModal.removeEventListener('shown.bs.modal', initCropper);

                    cropper = new Cropper(cropImage, {
                        aspectRatio: 1,
                        viewMode: 2,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: true,
                        preview: '#circlePreview',
                    });

                    document.getElementById('zoomIn').onclick = function() { cropper.zoom(0.1); };
                    document.getElementById('zoomOut').onclick = function() { cropper.zoom(-0.1); };

                    cropImage.addEventListener('wheel', function(e) {
                        e.preventDefault();
                        cropper.zoom(e.deltaY > 0 ? -0.1 : 0.1);
                    }, { passive: false });
                });
            };
            reader.readAsDataURL(file);
        });
    });

    // Apply crop
    document.getElementById('cropApplyBtn').addEventListener('click', function() {
        if (!cropper || !currentForm) return;

        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

                canvas.toBlob(function(blob) {
            const reader = new FileReader();
            reader.onloadend = function() {
                const base64 = reader.result;
                let hiddenInput = currentForm.querySelector('input[name="image_data"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'image_data';
                    currentForm.appendChild(hiddenInput);
                }
                hiddenInput.value = base64;

                // Clear file input and show preview
                if (currentFileInput) {
                    currentFileInput.value = '';
                    const previewContainer = currentFileInput.closest('.mb-3')?.querySelector('.image-preview-container');
                    if (previewContainer) {
                        previewContainer.innerHTML = '<div class="d-flex align-items-center gap-2 mt-2"><img src="' + base64 + '" alt="Preview" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;border:1px solid #dee2e6;"><button type="button" class="btn btn-outline-danger btn-sm remove-cropped-image" title="Remove photo">Remove</button></div>';
                        previewContainer.style.display = 'block';
                        previewContainer.querySelector('.remove-cropped-image')?.addEventListener('click', function() {
                            hiddenInput.value = '';
                            previewContainer.innerHTML = '';
                            previewContainer.style.display = 'none';
                        });
                    }
                }

                bootstrap.Modal.getInstance(document.getElementById('imageCropModal')).hide();
                cropper.destroy();
                cropper = null;
                currentForm = null;
                currentFileInput = null;
            };
            reader.readAsDataURL(blob);
        }, 'image/jpeg', 0.9);
    });

    // Cancel - reset
    document.getElementById('imageCropModal').addEventListener('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (currentFileInput) currentFileInput.value = '';
        currentForm = null;
        currentFileInput = null;
    });
});
</script>
