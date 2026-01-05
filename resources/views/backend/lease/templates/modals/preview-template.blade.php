<!-- Preview Template Modal -->
<div class="modal-content">
    <div class="modal-header bg-success">
        <div>
            <h5 class="modal-title text-white">Template Preview</h5>
            <small id="previewModalSubtitle" class="text-muted"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <!-- Options -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Preview Options</h6>
            </div>
            <div class="card-body">
                <div class="form-check">
                    <input 
                        type="checkbox" 
                        id="useSampleDataCheck"
                        class="form-check-input"
                        checked
                    >
                    <label class="form-check-label" for="useSampleDataCheck">
                        Use Sample Data
                    </label>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div id="previewAlert" class="d-none" role="alert"></div>

        <!-- Loading -->
        <div id="previewLoading" class="text-center py-5 d-none">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3">Loading preview...</p>
        </div>

        <!-- Preview Content -->
        <div id="previewContentDiv" class="card mb-4 d-none">
            <div class="card-header bg-light">
                <h6 class="mb-0">Document Preview</h6>
            </div>
            <div class="card-body bg-light" style="max-height: 400px; overflow-y: auto;">
                <div id="previewContentBody" class="text-dark" style="font-size: 0.9rem;"></div>
            </div>
        </div>

        <!-- Field Summary -->
        <div id="dataSummary" class="card d-none">
            <div class="card-header bg-light">
                <h6 class="mb-0">Data Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-4">
                        <div class="bg-light  p-3 text-center">
                            <small class="text-muted d-block">Tenant Data</small>
                            <strong id="tenantDataStatus">✗ Not provided</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light  p-3 text-center">
                            <small class="text-muted d-block">Property Data</small>
                            <strong id="propertyDataStatus">✗ Not provided</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light  p-3 text-center">
                            <small class="text-muted d-block">Lease Data</small>
                            <strong id="leaseDataStatus">✗ Not provided</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button 
            id="printPreviewBtn"
            type="button"
            disabled
            class="btn btn-info text-white"
        >
            <i class="bi bi-printer"></i> Print
        </button>
        <button 
            type="button"
            data-bs-dismiss="modal"
            class="btn btn-secondary"
        >
            Close
        </button>
    </div>
</div>
@push('scripts')
<script>
// Preview Template Handlers (jQuery) - Initialized in index.blade.php
$(function() {
    let currentPreviewData = null;

    // Checkbox change handler will be setup in index.blade.php
    $(document).on('change', '#useSampleDataCheck', function() {
        // loadPreview call will be made from index.blade.php
    });

    $(document).on('click', '#printPreviewBtn', function() {
        if (!currentPreviewData) return;
        const printWindow = window.open('', '', 'height=400,width=800');
        printWindow.document.write('<html><head><title>Print</title></head><body>');
        printWindow.document.write(currentPreviewData);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
});
</script>
@endpush