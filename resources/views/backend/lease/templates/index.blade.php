@extends('backend.app')

@section('title', 'Lease Templates')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Lease Templates</h1>
                        <p class="text-muted">Manage lease document templates and field mappings</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <button 
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#createTemplateModal">
                            <i class="bi bi-plus-lg"></i> New Template
                        </button>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <div class="row">
                    <!-- Filters Card -->
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <input 
                                            id="searchInput"
                                            type="text" 
                                            placeholder="Search templates..." 
                                            class="form-control"
                                        >
                                    </div>
                                    <div class="col-md-4">
                                        <select 
                                            id="filterActive"
                                            class="form-select"
                                        >
                                            <option value="">All Status</option>
                                            <option value="1">Active Only</option>
                                            <option value="0">Inactive Only</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertContainer" class="col-12"></div>

                    <!-- Templates Container -->
                    <div id="templatesContainer" class="col-12"></div>

                    <!-- Empty State -->
                    <div id="emptyState" class="col-12" style="display: none;">
                        <div class="card text-center py-5">
                            <div class="card-body">
                                <i class="bi bi-file-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No templates found</p>
                                <small class="text-muted">Create your first lease template to get started</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Template Modal -->
    <div class="modal fade" id="createTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            @include('backend.lease.templates.modals.create-template')
        </div>
    </div>

    <!-- View Template Modal -->
    <div class="modal fade" id="viewTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div id="viewTemplateContent"></div>
        </div>
    </div>

    <!-- Field Mapping Modal -->
    <div class="modal fade" id="fieldMappingModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div id="fieldMappingContent"></div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div id="previewContent"></div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            let allTemplates = [];
            let currentTemplate = null;
            let currentFieldMappings = [];
            const apiBaseUrl = '/admin/lease-templates';
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Initialize
            console.log('Template script Initialized....');
            
            loadTemplates();
            setupEventListeners();

            function setupEventListeners() {
                console.log("setup event Initialized___....");
                
                // Search and filter
                $('#searchInput').on('keyup', filterTemplates);
                $('#filterActive').on('change', filterTemplates);

                // Create template form submit
                $(document).on('submit', '#createTemplateForm', function(e) {
                    e.preventDefault();
                    console.log('Temaple submiting');
                    
                    submitCreateTemplate();
                });

                // Delete template
                $(document).on('click', '.delete-template', function() {
                    const templateId = $(this).data('template-id');
                    if (confirm('Are you sure you want to delete this template?')) {
                        deleteTemplate(templateId);
                    }
                });

                // View template
                $(document).on('click', '.view-template', function() {
                    const templateId = $(this).data('template-id');
                    viewTemplate(templateId);
                });

                // Edit field mappings
                $(document).on('click', '.edit-mappings', function() {
                    const templateId = $(this).data('template-id');
                    editFieldMappings(templateId);
                });

                // Preview template
                $(document).on('click', '.preview-template', function() {
                    const templateId = $(this).data('template-id');
                    previewTemplate(templateId);
                });

                // Field mapping modal button handlers
                $(document).on('click', '#suggestMappingsBtn', function() {
                    if (currentTemplate) suggestMappings();
                });

                $(document).on('click', '#extractPlaceholdersBtn', function() {
                    if (currentTemplate) extractPlaceholders();
                });

                $(document).on('click', '#toggleAddMappingBtn', function() {
                    $('#addMappingForm').toggleClass('d-none');
                });

                $(document).on('click', '#cancelAddMappingBtn', function() {
                    $('#addMappingForm').addClass('d-none');
                    $('#newFieldPlaceholder').val('');
                    $('#newFieldLabel').val('');
                    $('#newFieldType').val('TEXT');
                    $('#newDataSource').val('');
                    $('#newIsRequired').prop('checked', true);
                });

                $(document).on('click', '#addMappingBtn', function() {
                    addMappingLocally();
                });

                $(document).on('click', '.delete-mapping', function() {
                    const mappingId = $(this).data('mapping-id');
                    deleteMappingLocally(mappingId);
                });

                $(document).on('click', '#saveMappingsBtn', function() {
                    if (currentTemplate && currentFieldMappings.length > 0) {
                        saveMappings(currentTemplate.id);
                    }
                });

                $(document).on('click', '#editFieldMappingsBtn', function() {
                    if (currentTemplate) {
                        editFieldMappings(currentTemplate.id);
                    }
                });

                // Preview modal handlers
                $(document).on('change', '#useSampleDataCheck', function() {
                    if (currentTemplate) {
                        loadPreviewData(currentTemplate.id, $(this).prop('checked'));
                    }
                });
            }

            function loadTemplates() {
                $.ajax({
                    url: apiBaseUrl,
                    type: 'GET',
                    success: function(response) {
                        console.log('Template loading: '+ response.data);
                        
                        allTemplates = response.data.data || response.data;
                        filterTemplates();
                    },
                    error: function(xhr) {
                        showAlert('Failed to load templates', 'danger');
                        console.error(xhr);
                    }
                });
            }

            function filterTemplates() {
                const search = $('#searchInput').val().toLowerCase();
                const filterStatus = $('#filterActive').val();

                const filtered = allTemplates.filter(template => {
                    const matchesSearch = template.title.toLowerCase().includes(search) ||
                                        (template.uploaded_file_original_name && 
                                        template.uploaded_file_original_name.toLowerCase().includes(search));
                    const matchesStatus = filterStatus === '' || template.is_active == filterStatus;
                    return matchesSearch && matchesStatus;
                });

                renderTemplates(filtered);
            }

            function renderTemplates(templates) {
                const container = $('#templatesContainer');
                const emptyState = $('#emptyState');

                if (templates.length === 0) {
                    container.empty();
                    emptyState.show();
                    return;
                }

                emptyState.hide();
                let html = '';

                templates.forEach(template => {
                    const createdDate = new Date(template.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    const mappingCount = template.field_mappings ? template.field_mappings.length : 0;
                    const statusBadge = template.is_active ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-secondary">Inactive</span>';

                    html += `
                        <div class="col-12 mb-3">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title">${escapeHtml(template.title)}</h5>
                                        <p class="text-muted mb-0 small">
                                            ${template.document_type} • 
                                            ${mappingCount} field mappings
                                        </p>
                                    </div>
                                    <div>${statusBadge}</div>
                                </div>

                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted d-block">File</small>
                                            <code>${escapeHtml(template.uploaded_file_original_name)}</code>
                                        </div>
                                        <div class="col-sm-6 text-sm-end">
                                            <small class="text-muted d-block">Created</small>
                                            <span>${createdDate}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-light p-3">
                                        <small>${template.content ? template.content.substring(0, 150) + '...' : 'No content'}</small>
                                    </div>
                                </div>

                                <div class="card-footer bg-light">
                                    <div class="btn-group w-100" role="group">
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-outline-primary view-template"
                                            data-template-id="${template.id}"
                                        >
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-outline-warning edit-mappings"
                                            data-template-id="${template.id}"
                                        >
                                            <i class="bi bi-pencil"></i> Map Fields
                                        </button>
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-outline-success preview-template"
                                            data-template-id="${template.id}"
                                        >
                                            <i class="bi bi-eye-fill"></i> Preview
                                        </button>
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-outline-danger delete-template"
                                            data-template-id="${template.id}"
                                        >
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.html(html);
            }

            function submitCreateTemplate() {
                const formData = new FormData($('#createTemplateForm')[0]);
                console.log('Template create Start');
                
                $.ajax({
                    url: apiBaseUrl,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        showAlert('Template created successfully', 'success');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('createTemplateModal'));
                        modal.hide();
                        $('#createTemplateForm')[0].reset();
                        loadTemplates();
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Failed to create template';
                        showAlert(error, 'danger');
                    }
                });
            }

            function viewTemplate(templateId) {
                const template = allTemplates.find(t => t.id === templateId);
                console.log(template);
                
                if (!template) return;

                currentTemplate = template;
                renderViewTemplate(template);
                const modal = new bootstrap.Modal(document.getElementById('viewTemplateModal'));
                modal.show();
            }

            function renderViewTemplate(template) {
                const createdDate = new Date(template.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                const mappingCount = template.field_mappings ? template.field_mappings.length : 0;

                let mappingsHtml = '';
                if (template.field_mappings && template.field_mappings.length > 0) {
                    template.field_mappings.forEach(mapping => {
                        const requiredBadge = mapping.is_required ? '<span class="badge bg-danger">Required</span>' : '';
                        mappingsHtml += `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <code>${mapping.field_placeholder}</code>
                                        <p class="mb-1 small text-muted">${mapping.field_label}</p>
                                        <small class="text-muted">Source: ${mapping.tenant_data_source}</small>
                                    </div>
                                    <div class="ms-3">
                                        <span class="badge bg-primary">${mapping.field_type}</span>
                                        ${requiredBadge}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    mappingsHtml = '<div class="alert alert-info mb-0"><small>No field mappings configured yet</small></div>';
                }

                $('#viewTemplateContent').html(`
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <div>
                                <h5 class="modal-title">${escapeHtml(template.title)}</h5>
                                <small class="text-muted">File: ${escapeHtml(template.uploaded_file_original_name)}</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="bg-light p-3">
                                        <small class="text-muted d-block">File Type</small>
                                        <strong>${template.document_type}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3">
                                        <small class="text-muted d-block">Status</small>
                                        <strong>${template.is_active ? 'Active' : 'Inactive'}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3">
                                        <small class="text-muted d-block">Created</small>
                                        <strong>${createdDate}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3">
                                        <small class="text-muted d-block">Field Mappings</small>
                                        <strong>${mappingCount} mappings</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold">Document Content</h6>
                                <div class="bg-light p-3 border" style="max-height: 300px; overflow-y: auto;">
                                    <small>${template.content || 'No content'}</small>
                                </div>
                            </div>

                            <div>
                                <h6 class="fw-bold">Field Mappings</h6>
                                <div class="list-group">
                                    ${mappingsHtml}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button 
                                type="button"
                                class="btn btn-warning edit-mappings"
                                data-template-id="${template.id}"
                                data-bs-dismiss="modal"
                            >
                                <i class="bi bi-pencil"></i> Edit Field Mappings
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                `);
            }

            function editFieldMappings(templateId) {
                currentTemplate = allTemplates.find(t => t.id === templateId);
                if (!currentTemplate) return;

                $.ajax({
                    url: `${apiBaseUrl}/${templateId}/field-mappings`,
                    type: 'GET',
                    success: function(response) {
                        currentFieldMappings = response.data;
                        renderFieldMappingModal(currentTemplate, currentFieldMappings);
                        const modal = new bootstrap.Modal(document.getElementById('fieldMappingModal'));
                        modal.show();
                    },
                    error: function(xhr) {
                        showAlert('Failed to load field mappings', 'danger');
                    }
                });
            }

            function renderFieldMappingModal(template, mappings) {
                let mappingsHtml = '';
                if (mappings.length > 0) {
                    mappings.forEach(mapping => {
                        const requiredBadge = mapping.is_required ? '<span class="badge bg-danger">Required</span>' : '';
                        mappingsHtml += `
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <code>${mapping.field_placeholder}</code>
                                    <p class="mb-1 small text-muted">${mapping.field_label}</p>
                                    <small class="text-muted">Source: ${mapping.tenant_data_source}</small>
                                </div>
                                <div>
                                    <span class="badge bg-primary">${mapping.field_type}</span>
                                    ${requiredBadge}
                                    <button class="btn btn-sm btn-link text-danger delete-mapping" data-mapping-id="${mapping.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }

                $('#fieldMappingContent').html(`
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <div>
                                <h5 class="modal-title text-white">Field Mapping Configuration</h5>
                                <small class="text-muted">${escapeHtml(template.title)}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div id="fieldMappingAlert"></div>

                            <div class="btn-group w-100 mb-4" role="group">
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-info text-white"
                                    id="suggestMappingsBtn"
                                >
                                    <i class="bi bi-lightbulb"></i> Auto-Suggest
                                </button>
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-info text-white"
                                    id="extractPlaceholdersBtn"
                                >
                                    <i class="bi bi-arrow-down"></i> Extract
                                </button>
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-success"
                                    id="toggleAddMappingBtn"
                                >
                                    <i class="bi bi-plus"></i> Add Manual
                                </button>
                            </div>

                            <div id="addMappingForm" class="card mb-4 d-none">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Add Field Mapping</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label">Placeholder Format</label>
                                            <input 
                                                type="text" 
                                                class="form-control"
                                                id="newPlaceholder"
                                                placeholder="@{{FIELD_NAME}}"
                                            >
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Field Label</label>
                                            <input 
                                                type="text" 
                                                class="form-control"
                                                id="newFieldLabel"
                                                placeholder="Field Label"
                                            >
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Field Type</label>
                                            <select class="form-select" id="newFieldType">
                                                <option value="TEXT">Text</option>
                                                <option value="DATE">Date</option>
                                                <option value="AMOUNT">Amount</option>
                                                <option value="SIGNATURE">Signature</option>
                                                <option value="CUSTOM">Custom</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Data Source</label>
                                            <select class="form-select" id="newDataSource">
                                                <option value="">Select source...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            id="newIsRequired"
                                            checked
                                        >
                                        <label class="form-check-label" for="newIsRequired">
                                            Required Field
                                        </label>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-success" id="addMappingBtn">Add</button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="cancelAddMappingBtn">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold">Current Mappings (${mappings.length})</h6>
                            <div class="list-group">
                                ${mappingsHtml || '<div class="alert alert-info">No field mappings yet</div>'}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button 
                                type="button"
                                class="btn btn-primary"
                                id="saveMappingsBtn"
                            >
                                Save All Mappings
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                `);

                setupFieldMappingHandlers(template.id);
            }

            function setupFieldMappingHandlers(templateId) {
                // All handlers are now setup with event delegation in setupEventListeners()
                // This function is kept for compatibility
            }

            function suggestMappings() {
                if (!currentTemplate) return;
                $.ajax({
                    url: `${apiBaseUrl}/${currentTemplate.id}/field-mappings/suggestions`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        const suggestions = response.data;
                        currentFieldMappings = [
                            ...currentFieldMappings,
                            ...suggestions.filter(s => !currentFieldMappings.find(m => m.field_placeholder === s.field_placeholder))
                        ];
                        showFieldMappingAlert(suggestions.length + ' field mappings suggested', 'success');
                        renderFieldMappingModal(currentTemplate, currentFieldMappings);
                    },
                    error: function(xhr) {
                        showFieldMappingAlert('Failed to generate suggestions', 'danger');
                    }
                });
            }

            function extractPlaceholders() {
                if (!currentTemplate) return;
                $.ajax({
                    url: `${apiBaseUrl}/${currentTemplate.id}/extract-placeholders`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        showFieldMappingAlert(response.data.count + ' placeholders extracted', 'success');
                    },
                    error: function(xhr) {
                        showFieldMappingAlert('Failed to extract placeholders', 'danger');
                    }
                });
            }

            function addMappingLocally() {
                const placeholder = $('#newPlaceholder').val();
                const label = $('#newFieldLabel').val();
                const type = $('#newFieldType').val();
                const source = $('#newDataSource').val();
                const isRequired = $('#newIsRequired').is(':checked');

                if (!placeholder || !label || !source) {
                    showFieldMappingAlert('Please fill in all required fields', 'danger');
                    return;
                }

                currentFieldMappings.push({
                    id: Date.now(),
                    field_placeholder: placeholder,
                    field_label: label,
                    field_type: type,
                    tenant_data_source: source,
                    is_required: isRequired
                });

                $('#newPlaceholder').val('');
                $('#newFieldLabel').val('');
                $('#newFieldType').val('TEXT');
                $('#newDataSource').val('');
                $('#newIsRequired').prop('checked', true);
                $('#addMappingForm').addClass('d-none');

                showFieldMappingAlert('Mapping added (not saved yet)', 'info');
                renderFieldMappingModal(currentTemplate, currentFieldMappings);
            }

            function saveMappings(templateId) {
                const mappings = currentFieldMappings.map(m => ({
                    field_placeholder: m.field_placeholder,
                    field_label: m.field_label,
                    field_type: m.field_type,
                    tenant_data_source: m.tenant_data_source,
                    is_required: m.is_required
                }));

                $.ajax({
                    url: `${apiBaseUrl}/${templateId}/field-mappings/store`,
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: JSON.stringify({ mappings: mappings }),
                    success: function(response) {
                        showFieldMappingAlert('Field mappings saved successfully!', 'success');
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('fieldMappingModal'));
                            if (modal) modal.hide();
                            loadTemplates();
                        }, 1500);
                    },
                    error: function(xhr) {
                        showFieldMappingAlert('Failed to save field mappings', 'danger');
                    }
                });
            }

            function previewTemplate(templateId) {
                currentTemplate = allTemplates.find(t => t.id === templateId);
                if (!currentTemplate) return;

                $('#previewLoading').removeClass('d-none');
                $('#previewContentDiv').addClass('d-none');
                $('#dataSummary').addClass('d-none');
                $('#previewModalSubtitle').text('File: ' + (currentTemplate.uploaded_file_original_name || 'N/A'));

                $.ajax({
                    url: `${apiBaseUrl}/${templateId}/preview-with-data`,
                    type: 'GET',
                    data: { use_sample_data: true },
                    success: function(response) {
                        // console.log(response);
                        
                        const data = response.data;
                        $('#previewContentBody').html(data.content);
                        
                        // Update data summary
                        const dataUsed = data.data_used;
                        $('#tenantDataStatus').html(dataUsed.has_tenant_data ? '<span class="text-success">✓ Populated</span>' : '<span class="text-danger">✗ Not provided</span>');
                        $('#propertyDataStatus').html(dataUsed.has_property_data ? '<span class="text-success">✓ Populated</span>' : '<span class="text-danger">✗ Not provided</span>');
                        $('#leaseDataStatus').html(dataUsed.has_lease_data ? '<span class="text-success">✓ Populated</span>' : '<span class="text-danger">✗ Not provided</span>');

                        $('#previewLoading').addClass('d-none');
                        $('#previewContentDiv').removeClass('d-none');
                        $('#dataSummary').removeClass('d-none');
                        $('#printPreviewBtn').prop('disabled', false);

                        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                        modal.show();
                    },
                    error: function(xhr) {
                        showAlert('Failed to load preview', 'danger');
                        $('#previewLoading').addClass('d-none');
                    }
                });
            }

            function deleteTemplate(templateId) {
                $.ajax({
                    url: `${apiBaseUrl}/${templateId}/destroy`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        showAlert('Template deleted successfully', 'success');
                        loadTemplates();
                    },
                    error: function(xhr) {
                        showAlert('Failed to delete template', 'danger');
                    }
                });
            }

            function deleteMappingLocally(mappingId) {
                currentFieldMappings = currentFieldMappings.filter(m => m.id !== mappingId);
                showFieldMappingAlert('Mapping removed', 'info');
                renderFieldMappingModal(currentTemplate, currentFieldMappings);
            }

            function loadPreviewData(templateId, useSampleData) {
                $('#previewLoading').removeClass('d-none');
                $('#previewContentDiv').addClass('d-none');
                $('#dataSummary').addClass('d-none');

                $.ajax({
                    url: `${apiBaseUrl}/${templateId}/preview-with-data`,
                    type: 'GET',
                    data: { use_sample_data: useSampleData },
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        const data = response.data;
                        $('#previewContentBody').html(data.content);
                        
                        // Update data summary
                        const dataUsed = data.data_used;
                        $('#tenantDataStatus').html(dataUsed.has_tenant_data ? '<span class="text-success">✓ Populated</span>' : '<span class="text-danger">✗ Not provided</span>');
                        $('#propertyDataStatus').html(dataUsed.has_property_data ? '<span class="text-success">✓ Populated</span>' : '<span class="text-danger">✗ Not provided</span>');
                        $('#leaseDataStatus').html(dataUsed.has_lease_data ? '<span class="text-success">✓ Populated</span>' : '<span class="text-danger">✗ Not provided</span>');

                        $('#previewLoading').addClass('d-none');
                        $('#previewContentDiv').removeClass('d-none');
                        $('#dataSummary').removeClass('d-none');
                        $('#printPreviewBtn').prop('disabled', false);
                    },
                    error: function(xhr) {
                        showAlert('Failed to load preview', 'danger');
                        $('#previewLoading').addClass('d-none');
                    }
                });
            }

            function showAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    $('#alertContainer').fadeOut(() => {
                        $('#alertContainer').html('').show();
                    });
                }, 5000);
            }

            function showFieldMappingAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#fieldMappingAlert').html(alertHtml);
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }

            function getToken() {
                return csrfToken || localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
            }

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

        });
</script>
@endpush