<!-- View Template Modal -->
<div class="modal-content">
    <div class="modal-header bg-light">
        <div>
            <h5 id="viewModalTitle" class="modal-title"></h5>
            <small id="viewModalSubtitle" class="text-muted"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <!-- Template Info -->
        <div class="row g-3 mb-4" id="viewTemplateInfo">
        </div>

        <!-- Content Preview -->
        <div class="mb-4">
            <h6 class="fw-bold">Document Content</h6>
            <div class="bg-light  p-3 border" style="max-height: 300px; overflow-y: auto;">
                <small id="viewTemplateContent"></small>
            </div>
        </div>

        <!-- Field Mappings -->
        <div>
            <h6 class="fw-bold">Field Mappings</h6>
            <div id="viewFieldMappingsContainer"></div>
        </div>
    </div>

    <div class="modal-footer">
        <button 
            id="editFieldMappingsBtn"
            type="button"
            class="btn btn-warning"
        >
            <i class="bi bi-pencil"></i> Edit Field Mappings
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
