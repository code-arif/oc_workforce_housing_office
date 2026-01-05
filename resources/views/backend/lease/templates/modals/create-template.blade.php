<!-- Create Template Modal -->
<div class="modal-content">
    <div class="modal-header bg-primary">
        <h5 class="modal-title text-white">Create New Lease Template</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <form id="createTemplateForm" class="modal-body">
        <!-- Alert Messages -->
        <div id="createTemplateAlert" class="d-none" role="alert"></div>

        <div class="mb-3">
            <label for="templateTitle" class="form-label">Template Title</label>
            <input 
                type="text" 
                id="templateTitle"
                name="title"
                placeholder="e.g., Standard Residential Lease Agreement" 
                class="form-control"
                required
            >
        </div>

        <!-- Document Upload -->
        <div class="mb-3">
            <label for="documentUpload" class="form-label">Upload Document (PDF, DOCX)</label>
            <div 
                id="dropZone"
                class="border-2 border-dashed p-3 text-center cursor-pointer transition bg-light"
            >
                <i class="bi bi-cloud-upload text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted fw-medium mt-2">Drag and drop your document here</p>
                <p class="text-muted small">or click to browse</p>
                <input 
                    type="file" 
                    name="document"
                    accept=".pdf,.docx,.doc"
                    class="d-none"
                    id="documentUpload"
                >
            </div>
            <small id="uploadedFileName" class="d-none text-success d-block mt-2">
                <i class="bi bi-check-circle"></i> <span id="fileName"></span>
            </small>
        </div>

        <!-- Active Status -->
        <div class="form-check mb-3">
            <input 
                type="checkbox" 
                id="isActive"
                name="is_active"
                class="form-check-input"
                checked
            >
            <label for="isActive" class="form-check-label">Make this template active immediately</label>
        </div>

        <div class="modal-footer">
            <button 
                type="button"
                data-bs-dismiss="modal"
                class="btn btn-secondary"
            >
                Cancel
            </button>
            <button 
                type="submit"
                id="submitCreateBtn"
                class="btn btn-primary"
            >
                <span id="createBtnText"><i class="bi bi-plus-circle"></i> Create Template</span>
                <span id="createBtnLoading" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2"></span>Creating...
                </span>
            </button>
        </div>
    </form>
</div>
@push('temp_scripts')
<script>
    console.log('Create Template Initialized....');
    
// Create Template Modal Handlers (jQuery)
$(function() {
    let selectedFile = null;

    // Drop zone handlers
    $('#dropZone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('bg-primary bg-opacity-10 border-primary').removeClass('bg-light border-secondary');
    });

    $('#dropZone').on('dragleave', function() {
        $(this).removeClass('bg-primary bg-opacity-10 border-primary').addClass('bg-light border-secondary');
    });

    $('#dropZone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('bg-primary bg-opacity-10 border-primary').addClass('bg-light border-secondary');
        handleFileSelect(e.originalEvent.dataTransfer.files[0]);
    });

    // Click to upload (but don't re-trigger from the input itself)
    $('#dropZone').on('click', function(e) {
        // Only trigger if the click target is not the file input
        if (e.target.id !== 'documentUpload') {
            $('#documentUpload').click();
        }
    });

    $('#documentUpload').on('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    }).on('click', function(e) {
        // Prevent the click from bubbling back to dropZone
        e.stopPropagation();
    });

    function handleFileSelect(file) {
        if (!file) return;
        selectedFile = file;
        $('#fileName').text(file.name);
        $('#uploadedFileName').removeClass('d-none');
    }

    // Form submission handled in index.blade.php
});
</script>
@endpush