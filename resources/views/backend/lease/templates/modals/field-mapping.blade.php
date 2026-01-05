<!-- Field Mapping Modal -->
<div class="modal-content">
    <div class="modal-header bg-warning">
        <div>
            <h5 class="modal-title text-white">Field Mapping Configuration</h5>
            <small id="mappingModalSubtitle" class="text-muted"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <!-- Alerts -->
        <div id="fieldMappingAlert" class="d-none" role="alert"></div>

        <!-- Action Buttons -->
        <div class="btn-group w-100 mb-4" role="group">
            <button 
                id="suggestMappingsBtn"
                type="button"
                class="btn btn-sm btn-info text-white"
            >
                <i class="bi bi-lightbulb"></i> Auto-Suggest
            </button>
            <button 
                id="extractPlaceholdersBtn"
                type="button"
                class="btn btn-sm btn-info text-white"
            >
                <i class="bi bi-arrow-down"></i> Extract Placeholders
            </button>
            <button 
                id="toggleAddMappingBtn"
                type="button"
                class="btn btn-sm btn-success text-white"
            >
                <i class="bi bi-plus"></i> Add Manual Mapping
            </button>
        </div>

        <!-- Add Manual Mapping Form -->
        <div id="addMappingForm" class="card mb-4 d-none">
            <div class="card-header bg-light">
                <h6 class="mb-0">Add Field Mapping</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Placeholder Format</label>
                        <input 
                            type="text" 
                            id="newFieldPlaceholder"
                            placeholder="@{{FIELD_NAME}}" 
                            class="form-control form-control-sm"
                        >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Field Label</label>
                        <input 
                            type="text" 
                            id="newFieldLabel"
                            placeholder="Field Label" 
                            class="form-control form-control-sm"
                        >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Field Type</label>
                        <select id="newFieldType" class="form-select form-select-sm">
                            <option value="TEXT">Text</option>
                            <option value="DATE">Date</option>
                            <option value="AMOUNT">Amount</option>
                            <option value="SIGNATURE">Signature</option>
                            <option value="CUSTOM">Custom</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Data Source</label>
                        <select id="newDataSource" class="form-select form-select-sm">
                            <option value="">Select source...</option>
                        </select>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="newIsRequired" class="form-check-input" checked>
                    <label for="newIsRequired" class="form-check-label">Required Field</label>
                </div>
                <div class="d-flex gap-2">
                    <button 
                        id="addMappingBtn"
                        type="button"
                        class="btn btn-sm btn-success"
                    >
                        Add Mapping
                    </button>
                    <button 
                        id="cancelAddMappingBtn"
                        type="button"
                        class="btn btn-sm btn-secondary"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Extracted Placeholders -->
        <div id="extractedPlaceholdersDiv" class="bg-light border rounded p-3 mb-4 d-none">
            <h6 class="mb-3">Extracted Placeholders</h6>
            <div id="placeholdersContainer" class="row g-2"></div>
        </div>

        <!-- Current Mappings -->
        <div>
            <h6 class="mb-3">
                Current Mappings 
                <span id="mappingsCount" class="text-muted small">(0 mappings)</span>
            </h6>
            
            <div id="fieldMappingsContainer">
                <div class="text-muted text-center py-4">No field mappings yet. Use auto-suggest or add them manually.</div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="modal-footer">
        <button 
            id="saveMappingsBtn"
            type="button"
            class="btn btn-primary"
        >
            <span id="saveBtnText">Save All Mappings</span>
            <span id="saveBtnLoading" class="d-none">
                <span class="spinner-border spinner-border-sm me-2"></span>Saving...
            </span>
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
