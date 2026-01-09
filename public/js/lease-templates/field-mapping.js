// Field Mapping Page JavaScript
$(document).ready(function() {
    const templateId = $('#templateId').val();
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const apiBaseUrl = `/admin/lease-templates/${templateId}`;
    
    let currentFieldMappings = [];
    let availableDataSources = {};

    // Initialize
    init();

    function init() {
        loadAvailableDataSources();
        loadExistingMappings();
        setupEventListeners();
    }

    function setupEventListeners() {
        // Suggest mappings
        $('#suggestMappingsBtn').on('click', suggestMappings);

        // Extract placeholders
        $('#extractPlaceholdersBtn').on('click', extractPlaceholders);

        // Toggle add mapping form
        $('#toggleAddMappingBtn').on('click', function() {
            $('#addMappingForm').toggleClass('d-none');
        });

        // Cancel add mapping
        $('#cancelAddMappingBtn').on('click', function() {
            resetAddMappingForm();
            $('#addMappingForm').addClass('d-none');
        });

        // Add mapping
        $('#addMappingBtn').on('click', addMappingLocally);

        // Save all mappings
        $('#saveMappingsBtn').on('click', saveMappings);

        // Delete mapping (delegated)
        $(document).on('click', '.delete-mapping-btn', function() {
            const mappingId = $(this).data('mapping-id');
            deleteMappingLocally(mappingId);
        });

        // Edit mapping (delegated)
        $(document).on('click', '.edit-mapping-btn', function() {
            const mappingId = $(this).data('mapping-id');
            editMappingLocally(mappingId);
        });
    }

    function loadAvailableDataSources() {
        $.ajax({
            url: `${apiBaseUrl}/field-mappings/available-data-sources`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    availableDataSources = response.data;
                    populateDataSourceSelect();
                }
            },
            error: function(xhr) {
                showAlert('Failed to load data sources', 'danger');
                console.error('Error:', xhr);
            }
        });
    }

    function populateDataSourceSelect() {
        const select = $('#newDataSource');
        select.empty();
        select.append('<option value="">-- Select Data Source --</option>');

        Object.entries(availableDataSources).forEach(([entity, fields]) => {
            const optgroup = $('<optgroup>').attr('label', entity);
            Object.entries(fields).forEach(([key, label]) => {
                optgroup.append($('<option>').attr('value', key).text(label));
            });
            select.append(optgroup);
        });
    }

    function loadExistingMappings() {
        $.ajax({
            url: `${apiBaseUrl}/field-mappings`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    currentFieldMappings = response.data;
                    renderMappingsList();
                    updateMappingCount();
                }
            },
            error: function(xhr) {
                console.error('Error loading mappings:', xhr);
            }
        });
    }

    function renderMappingsList() {
        const container = $('#fieldMappingsContainer');

        if (currentFieldMappings.length === 0) {
            container.html(`
                <div class="text-center text-muted py-4">
                    <p>No field mappings yet. Add your first mapping or use the Suggest Mappings feature.</p>
                </div>
            `);
            return;
        }

        let html = '<div class="list-group">';

        currentFieldMappings.forEach((mapping) => {
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <code class="bg-light px-2 py-1 rounded">${escapeHtml(mapping.field_placeholder)}</code>
                                <span class="badge bg-info">${escapeHtml(mapping.field_type)}</span>
                                ${mapping.is_required ? '<span class="badge bg-danger">Required</span>' : '<span class="badge bg-secondary">Optional</span>'}
                            </div>
                            <p class="mb-1 mt-2">
                                <strong>${escapeHtml(mapping.field_label)}</strong>
                            </p>
                            <small class="text-muted">Data Source: ${escapeHtml(mapping.tenant_data_source)}</small>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            <button type="button" class="btn btn-sm btn-outline-primary edit-mapping-btn" data-mapping-id="${mapping.id}">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-mapping-btn" data-mapping-id="${mapping.id}">
                                <i class="ri-delete-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.html(html);
    }

    function addMappingLocally() {
        const placeholder = $('#newFieldPlaceholder').val().trim();
        const label = $('#newFieldLabel').val().trim();
        const type = $('#newFieldType').val();
        const source = $('#newDataSource').val();
        const isRequired = $('#newIsRequired').is(':checked');

        // Validation
        if (!placeholder) {
            showAlert('Placeholder is required', 'warning');
            return;
        }
        if (!label) {
            showAlert('Label is required', 'warning');
            return;
        }
        if (!source) {
            showAlert('Data source is required', 'warning');
            return;
        }

        // Validate placeholder format
        if (!/^\{\{[A-Z_]+(\|.*?)?\}\}$/.test(placeholder)) {
            showAlert('Placeholder must be in format {{PLACEHOLDER_NAME}}', 'warning');
            return;
        }

        // Add to array (with temporary ID if new)
        const mapping = {
            id: Date.now(), // Temporary ID for new mappings
            field_placeholder: placeholder,
            field_label: label,
            field_type: type,
            tenant_data_source: source,
            is_required: isRequired
        };

        currentFieldMappings.push(mapping);
        resetAddMappingForm();
        $('#addMappingForm').addClass('d-none');
        renderMappingsList();
        updateMappingCount();
        showAlert('Mapping added (not saved yet)', 'info');
    }

    function editMappingLocally(mappingId) {
        const mapping = currentFieldMappings.find(m => m.id == mappingId);
        if (!mapping) return;

        // Populate form with mapping data
        $('#newFieldPlaceholder').val(mapping.field_placeholder);
        $('#newFieldLabel').val(mapping.field_label);
        $('#newFieldType').val(mapping.field_type);
        $('#newDataSource').val(mapping.tenant_data_source);
        $('#newIsRequired').prop('checked', mapping.is_required);

        // Remove the mapping from the array
        currentFieldMappings = currentFieldMappings.filter(m => m.id != mappingId);

        // Show form
        $('#addMappingForm').removeClass('d-none');
        renderMappingsList();
        updateMappingCount();

        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#addMappingForm').offset().top - 100
        }, 300);
    }

    function deleteMappingLocally(mappingId) {
        if (!confirm('Are you sure you want to delete this mapping?')) {
            return;
        }

        currentFieldMappings = currentFieldMappings.filter(m => m.id != mappingId);
        renderMappingsList();
        updateMappingCount();
        showAlert('Mapping removed (not saved yet)', 'warning');
    }

    function resetAddMappingForm() {
        $('#newFieldPlaceholder').val('');
        $('#newFieldLabel').val('');
        $('#newFieldType').val('TEXT');
        $('#newDataSource').val('');
        $('#newIsRequired').prop('checked', true);
    }

    function updateMappingCount() {
        $('#mappingCount').text(`${currentFieldMappings.length} mapping${currentFieldMappings.length !== 1 ? 's' : ''}`);
    }

    function saveMappings() {
        if (currentFieldMappings.length === 0) {
            showAlert('Add at least one field mapping before saving', 'warning');
            return;
        }

        const mappings = currentFieldMappings.map(m => ({
            field_placeholder: m.field_placeholder,
            field_label: m.field_label,
            field_type: m.field_type,
            tenant_data_source: m.tenant_data_source,
            is_required: m.is_required
        }));

        console.log(mappings);
        
        $.ajax({
            url: `${apiBaseUrl}/field-mappings/store`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: JSON.stringify({ mappings: mappings }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    showAlert('Field mappings saved successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = `/admin/lease-templates/list/index`;
                    }, 1500);
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.error || 'Failed to save field mappings';
                showAlert(errorMessage, 'danger');
                console.error('Error:', xhr);
            }
        });
    }

    function suggestMappings() {
        $.ajax({
            url: `${apiBaseUrl}/field-mappings/suggestions`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    currentFieldMappings = response.data;
                    renderMappingsList();
                    updateMappingCount();
                    showAlert(`${response.data.length} mappings suggested. Review and save when ready.`, 'info');
                }
            },
            error: function(xhr) {
                showAlert('Failed to generate suggestions', 'danger');
                console.error('Error:', xhr);
            }
        });
    }

    function extractPlaceholders() {
        $.ajax({
            url: `${apiBaseUrl}/extract-placeholders`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    const placeholders = response.data.placeholders;
                    if (placeholders.length === 0) {
                        showAlert('No placeholders found in template', 'info');
                        return;
                    }

                    let message = `Found ${placeholders.length} placeholder(s):\n\n`;
                    placeholders.forEach(ph => {
                        message += `• ${ph}\n`;
                    });

                    alert(message);
                    showAlert('Use the Suggest Mappings feature to create field mappings for these placeholders', 'info');
                }
            },
            error: function(xhr) {
                showAlert('Failed to extract placeholders', 'danger');
                console.error('Error:', xhr);
            }
        });
    }

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('#alertContainer').fadeOut(function() {
                $(this).html('').fadeIn();
            });
        }, 5000);
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
});
