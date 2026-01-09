// Preview Page JavaScript - Phase 3: Document Generation with Field Mapping Display
$(document).ready(function() {
    const templateId = $('#templateId').val();
    const leaseId = $('#leaseId').val(); // Add this to the view
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const apiBaseUrl = `/admin/lease-templates/${templateId}`;
    const docApiUrl = `/admin/lease-documents/leases/${leaseId}`;

    let currentTemplate = null;
    let fieldMappings = [];
    let populatedData = null;

    // Initialize
    init();

    function init() {
        setupEventListeners();
        loadFieldMappings();
        loadPreview(false);
    }

    function setupEventListeners() {
        $('#useSampleDataCheck').on('change', function() {
            const useSampleData = $(this).is(':checked');
            loadPreview(useSampleData);
        });

        // Toggle field mapping display
        $(document).on('click', '.toggle-mapping-details', function() {
            $(this).closest('.mapping-card').find('.mapping-details').slideToggle();
        });
    }

    /**
     * Load field mappings for this template
     */
    function loadFieldMappings() {
        $.ajax({
            url: `${apiBaseUrl}/field-mappings`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success && response.data) {
                    fieldMappings = response.data;
                    renderMappingsSidebar();
                }
            },
            error: function(xhr) {
                console.error('Error loading field mappings:', xhr);
            }
        });
    }

    /**
     * Load preview with actual data if lease is available
     */
    function loadPreview(useSampleData) {
        showLoading();

        if (leaseId) {
            // Generate document using actual lease data
            loadActualDocumentPreview(useSampleData);
        } else {
            // Generate template preview with sample data
            loadTemplatePreview(useSampleData);
        }
    }

    /**
     * Load preview with actual lease data and field population info
     */
    function loadActualDocumentPreview(useSampleData) {
        $.ajax({
            url: `${docApiUrl}/preview`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                template_id: templateId
            }),
            success: function(response) {
                if (response.success) {
                    populatedData = response.data;
                    
                    // Display the populated content
                    displayPreview(response.data.content);
                    
                    // Display data population info
                    displayDataPopulationInfo(response.data);
                    
                    hideLoading();
                } else {
                    showError(response.message || 'Failed to load preview');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while loading the preview';
                showError(errorMessage);
                console.error('Error:', xhr);
            }
        });
    }

    /**
     * Load template preview (when no lease available)
     */
    function loadTemplatePreview(useSampleData) {
        const url = useSampleData 
            ? `${apiBaseUrl}/preview-with-data`
            : `${apiBaseUrl}/preview`;

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    currentTemplate = response.data;
                    
                    if (useSampleData && response.data.sample_data) {
                        displaySampleData(response.data.sample_data);
                    }

                    displayPreview(response.data.content || response.data.preview);
                    hideLoading();
                } else {
                    showError(response.message || 'Failed to load preview');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while loading the preview';
                showError(errorMessage);
                console.error('Error:', xhr);
            }
        });
    }

    /**
     * Display preview with annotations showing field replacements
     */
    function displayPreview(content) {
        $('#previewError').addClass('d-none');
        $('#previewContentDiv').removeClass('d-none');
        
        // If we have field mappings, annotate the preview
        let annotatedContent = content;
        if (fieldMappings.length > 0 && populatedData) {
            annotatedContent = annotatePreviewWithMappings(content);
        }

        // Create a safe container for the preview
        const previewContainer = $('<div>')
            .html(annotatedContent)
            .css({
                'padding': '20px',
                'background': '#fff',
                'border': '1px solid #dee2e6',
                'border-radius': '4px',
                'line-height': '1.6'
            });

        $('#previewContentDiv').empty().append(previewContainer);
    }

    /**
     * Annotate preview content showing where fields were replaced
     */
    function annotatePreviewWithMappings(content) {
        let annotated = content;

        fieldMappings.forEach((mapping, index) => {
            // Get the actual value that was inserted
            const dataSource = mapping.tenant_data_source;
            const value = extractDataValue(dataSource);
            
            if (value) {
                // Wrap replaced values with a highlighted marker (optional)
                // This helps visualize where data was populated
                const displayValue = value.substring ? value.substring(0, 50) : String(value);
                
                // Add a data attribute to help identify replaced content
                // (In practice, you might want to use unique markers in the template first)
            }
        });

        return annotated;
    }

    /**
     * Extract value from populated data using dot notation
     */
    function extractDataValue(dataSource) {
        if (!populatedData) return null;
        
        const data = populatedData.tenant_data || {};
        const parts = dataSource.split('.');
        
        let value = populatedData;
        
        // Try to find in each data category
        const categories = [populatedData.tenant_data, populatedData.property_data, populatedData.lease_data];
        
        for (let category of categories) {
            if (!category) continue;
            
            let temp = category;
            let found = true;
            
            for (let part of parts.slice(1)) {
                if (temp && typeof temp === 'object' && temp[part] !== undefined) {
                    temp = temp[part];
                } else {
                    found = false;
                    break;
                }
            }
            
            if (found && temp !== undefined) {
                return temp;
            }
        }
        
        return null;
    }

    /**
     * Render mappings sidebar showing field-to-data mapping
     */
    function renderMappingsSidebar() {
        if (fieldMappings.length === 0) {
            $('#mappingsSidebar').html(
                '<div class="alert alert-info">No field mappings configured</div>'
            );
            return;
        }

        let html = `
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-diagram-3"></i> Field Mappings (${fieldMappings.length})
                    </h6>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
        `;

        fieldMappings.forEach((mapping, index) => {
            const dataValue = extractDataValue(mapping.tenant_data_source);
            const displayValue = dataValue ? String(dataValue).substring(0, 40) : '<span class="text-danger">No Data</span>';
            const requiredBadge = mapping.is_required ? '<span class="badge bg-danger">Required</span>' : '<span class="badge bg-secondary">Optional</span>';
            const typeIcon = getFieldTypeIcon(mapping.field_type);

            html += `
                <div class="mapping-card border  p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">
                                ${typeIcon} 
                                <strong>${escapeHtml(mapping.field_label)}</strong>
                            </h6>
                            <small class="text-muted">Placeholder:</small><br>
                            <code class="bg-light p-1  small">${escapeHtml(mapping.field_placeholder)}</code>
                        </div>
                        <div>${requiredBadge}</div>
                    </div>

                    <div class="mapping-details d-none pt-2 border-top">
                        <div class="mb-2">
                            <small class="text-muted">Data Source:</small><br>
                            <code class="bg-light p-1  small">${escapeHtml(mapping.tenant_data_source)}</code>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Field Type:</small><br>
                            <span class="badge bg-info">${escapeHtml(mapping.field_type)}</span>
                        </div>

                        <div class="alert alert-light p-2 mb-0">
                            <small class="text-muted">Populated Value:</small><br>
                            <strong class="text-success">${displayValue}</strong>
                        </div>
                    </div>

                    <button class="btn btn-sm btn-outline-secondary toggle-mapping-details mt-2 w-100">
                        <i class="bi bi-chevron-down"></i> Show Details
                    </button>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;

        $('#mappingsSidebar').html(html);
    }

    /**
     * Display data population info
     */
    function displayDataPopulationInfo(data) {
        if (!data.data_used) return;

        let infoHtml = `
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Data Population Summary</h6>
                <div class="row mt-2">
                    <div class="col-md-4 text-center">
                        <i class="bi bi-person" style="font-size: 1.5rem;"></i><br>
                        <small>Tenant Data</small><br>
                        <span class="badge ${data.data_used.has_tenant_data ? 'bg-success' : 'bg-secondary'}">
                            ${data.data_used.has_tenant_data ? '✓ Populated' : '✗ Missing'}
                        </span>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-building" style="font-size: 1.5rem;"></i><br>
                        <small>Property Data</small><br>
                        <span class="badge ${data.data_used.has_property_data ? 'bg-success' : 'bg-secondary'}">
                            ${data.data_used.has_property_data ? '✓ Populated' : '✗ Missing'}
                        </span>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-file-earmark-text" style="font-size: 1.5rem;"></i><br>
                        <small>Lease Data</small><br>
                        <span class="badge ${data.data_used.has_lease_data ? 'bg-success' : 'bg-secondary'}">
                            ${data.data_used.has_lease_data ? '✓ Populated' : '✗ Missing'}
                        </span>
                    </div>
                </div>
            </div>
        `;

        $('#dataSummary').html(infoHtml).removeClass('d-none');
    }

    /**
     * Get icon for field type
     */
    function getFieldTypeIcon(fieldType) {
        const icons = {
            'TEXT': '<i class="bi bi-type"></i>',
            'DATE': '<i class="bi bi-calendar"></i>',
            'AMOUNT': '<i class="bi bi-currency-dollar"></i>',
            'SIGNATURE': '<i class="bi bi-pen"></i>',
            'CUSTOM': '<i class="bi bi-gear"></i>'
        };
        return icons[fieldType] || '<i class="bi bi-file"></i>';
    }

    function displaySampleData(sampleData) {
        $('#dataSummary').removeClass('d-none');
        const list = $('#sampleDataList').empty();

        Object.entries(sampleData).forEach(([key, value]) => {
            const item = $('<div>')
                .addClass('mb-2 p-2 bg-light ')
                .html(`
                    <small>
                        <strong>${escapeHtml(key)}:</strong><br>
                        ${escapeHtml(String(value).substring(0, 50))}${String(value).length > 50 ? '...' : ''}
                    </small>
                `);
            list.append(item);
        });
    }

    function showLoading() {
        $('#previewLoading').removeClass('d-none');
        $('#previewContentDiv').addClass('d-none');
        $('#previewError').addClass('d-none');
    }

    function hideLoading() {
        $('#previewLoading').addClass('d-none');
    }

    function showError(message) {
        $('#previewLoading').addClass('d-none');
        $('#previewContentDiv').addClass('d-none');
        $('#previewError').removeClass('d-none');
        $('#previewErrorMessage').text(message);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});
