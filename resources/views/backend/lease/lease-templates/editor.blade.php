@extends('backend.app')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Template Editor</h1>
                    <p class="text-muted mb-0">{{ $template->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="button" class="btn btn-success" id="saveTemplateBtn">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Left Sidebar - Placeholder Tools -->
                <div class="col-lg-3">
                    <div class="card sticky-sidebar">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-cubes"></i> Placeholder Fields</h6>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small px-2 mb-2">
                                <i class="fas fa-info-circle"></i> Drag fields onto the document
                            </p>

                            <!-- Placeholder Library -->
                            <div class="placeholder-library">
                                <!-- Tenant Fields -->
                                <div class="field-group mb-2">
                                    <div class="field-group-header" data-bs-toggle="collapse" data-bs-target="#tenantFields">
                                        <i class="fas fa-user"></i> Tenant Info
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="tenantFields">
                                        <div class="draggable-field" draggable="true" data-field="tenant_name" data-label="Tenant Name" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Tenant Name
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="tenant_email" data-label="Tenant Email" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Tenant Email
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="tenant_phone" data-label="Tenant Phone" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Tenant Phone
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="tenant_address" data-label="Tenant Address" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Tenant Address
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Fields -->
                                <div class="field-group mb-2">
                                    <div class="field-group-header" data-bs-toggle="collapse" data-bs-target="#propertyFields">
                                        <i class="fas fa-building"></i> Property
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="propertyFields">
                                        <div class="draggable-field" draggable="true" data-field="property_address" data-label="Property Address" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Property Address
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="property_type" data-label="Property Type" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Property Type
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="room_number" data-label="Room/Unit #" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Room/Unit #
                                        </div>
                                    </div>
                                </div>

                                <!-- Lease Terms -->
                                <div class="field-group mb-2">
                                    <div class="field-group-header" data-bs-toggle="collapse" data-bs-target="#leaseFields">
                                        <i class="fas fa-file-contract"></i> Lease Terms
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="leaseFields">
                                        <div class="draggable-field" draggable="true" data-field="lease_start_date" data-label="Start Date" data-type="date">
                                            <i class="fas fa-grip-vertical"></i> Start Date
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="lease_end_date" data-label="End Date" data-type="date">
                                            <i class="fas fa-grip-vertical"></i> End Date
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="lease_term" data-label="Lease Term" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Lease Term
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="monthly_rent" data-label="Monthly Rent" data-type="currency">
                                            <i class="fas fa-grip-vertical"></i> Monthly Rent
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="security_deposit" data-label="Security Deposit" data-type="currency">
                                            <i class="fas fa-grip-vertical"></i> Security Deposit
                                        </div>
                                    </div>
                                </div>

                                <!-- Landlord Fields -->
                                <div class="field-group mb-2">
                                    <div class="field-group-header" data-bs-toggle="collapse" data-bs-target="#landlordFields">
                                        <i class="fas fa-user-tie"></i> Landlord
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="landlordFields">
                                        <div class="draggable-field" draggable="true" data-field="landlord_name" data-label="Landlord Name" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Landlord Name
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="landlord_email" data-label="Landlord Email" data-type="text">
                                            <i class="fas fa-grip-vertical"></i> Landlord Email
                                        </div>
                                    </div>
                                </div>

                                <!-- Signatures -->
                                <div class="field-group mb-2">
                                    <div class="field-group-header" data-bs-toggle="collapse" data-bs-target="#signatureFields">
                                        <i class="fas fa-signature"></i> Signatures
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="signatureFields">
                                        <div class="draggable-field signature-field" draggable="true" data-field="landlord_signature" data-label="Landlord Signature" data-type="signature">
                                            <i class="fas fa-grip-vertical"></i> Landlord Signature
                                        </div>
                                        <div class="draggable-field signature-field" draggable="true" data-field="tenant_signature" data-label="Tenant Signature" data-type="signature">
                                            <i class="fas fa-grip-vertical"></i> Tenant Signature
                                        </div>
                                        <div class="draggable-field" draggable="true" data-field="date_signed" data-label="Date Signed" data-type="date">
                                            <i class="fas fa-grip-vertical"></i> Date Signed
                                        </div>
                                    </div>
                                </div>

                                <!-- Other -->
                                <div class="field-group">
                                    <div class="field-group-header" data-bs-toggle="collapse" data-bs-target="#otherFields">
                                        <i class="fas fa-ellipsis-h"></i> Other
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="otherFields">
                                        <div class="draggable-field" draggable="true" data-field="current_date" data-label="Current Date" data-type="date">
                                            <i class="fas fa-grip-vertical"></i> Current Date
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placed Fields Panel -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-list-check"></i> Placed Fields</h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="placedFieldsList" class="placed-fields-list">
                                <p class="text-muted small text-center py-3">No fields placed yet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Document Viewer -->
                <div class="col-lg-9">
                    <!-- Toolbar -->
                    <div class="card toolbar-card mb-3">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-primary">{{ strtoupper($template->file_type) }}</span>
                                    <span class="text-muted">{{ $template->original_filename }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Zoom Controls -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOut">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="btn btn-sm btn-outline-secondary" id="zoomLevel">100%</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomIn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- Page Navigation -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="prevPage">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <span class="btn btn-sm btn-outline-secondary" id="pageIndicator">
                                            Page <span id="currentPage">1</span> / <span id="totalPages">1</span>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="nextPage">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PDF Viewer Container -->
                    <div class="card document-viewer-card">
                        <div class="card-body p-0">
                            <div id="documentContainer" class="document-container">
                                <div id="pdfViewer" class="pdf-viewer">
                                    <!-- PDF pages will be rendered here -->
                                </div>
                                <!-- Placeholder overlay layer -->
                                <div id="placeholderOverlay" class="placeholder-overlay">
                                    <!-- Draggable placeholders will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Field Properties Modal -->
<div class="modal fade" id="fieldPropertiesModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Field Properties</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Field Label</label>
                    <input type="text" class="form-control form-control-sm" id="fieldLabel" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Width (px)</label>
                    <input type="number" class="form-control form-control-sm" id="fieldWidth" min="50" max="500">
                </div>
                <div class="mb-3">
                    <label class="form-label">Height (px)</label>
                    <input type="number" class="form-control form-control-sm" id="fieldHeight" min="20" max="200">
                </div>
                <div class="mb-3">
                    <label class="form-label">Font Size (px)</label>
                    <input type="number" class="form-control form-control-sm" id="fieldFontSize" min="8" max="36" value="12">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" id="deleteFieldBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="applyFieldBtn">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- PDF.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css">
<style>
    .sticky-sidebar {
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }

    /* Field Groups */
    .field-group-header {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        background: #f8f9fa;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .field-group-header i:first-child {
        margin-right: 8px;
        color: #667eea;
    }

    .field-group-header:hover {
        background: #f1f3f5;
    }

    /* Draggable Fields */
    .draggable-field {
        padding: 8px 12px;
        font-size: 12px;
        cursor: grab;
        border-bottom: 1px solid #f1f3f5;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }

    .draggable-field:hover {
        background: #e8f4fd;
    }

    .draggable-field i {
        color: #9ca3af;
        margin-right: 8px;
        font-size: 10px;
    }

    .draggable-field.signature-field {
        background: #fef3c7;
    }

    .draggable-field.signature-field:hover {
        background: #fde68a;
    }

    /* Document Container */
    .document-container {
        position: relative;
        background: #525659;
        min-height: 700px;
        overflow: auto;
        display: flex;
        justify-content: center;
        padding: 20px;
    }

    .pdf-viewer {
        position: relative;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    /* PDF Page */
    .pdf-page-wrapper {
        position: relative;
        margin-bottom: 20px;
    }

    .pdf-page-wrapper:last-child {
        margin-bottom: 0;
    }

    .pdf-page-wrapper canvas {
        display: block;
    }

    /* Placeholder Overlay */
    .placeholder-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
    }

    /* Placed Placeholder */
    .placed-placeholder {
        position: absolute;
        background: rgba(102, 126, 234, 0.2);
        border: 2px dashed #667eea;
        border-radius: 4px;
        cursor: move;
        pointer-events: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #667eea;
        font-weight: 600;
        min-width: 80px;
        min-height: 24px;
        padding: 4px 8px;
        user-select: none;
        z-index: 100;
    }

    .placed-placeholder:hover {
        background: rgba(102, 126, 234, 0.4);
        border-style: solid;
    }

    .placed-placeholder.signature {
        background: rgba(245, 158, 11, 0.2);
        border-color: #f59e0b;
        color: #f59e0b;
        min-width: 150px;
        min-height: 50px;
    }

    .placed-placeholder.active {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.2);
        z-index: 101;
    }

    .placed-placeholder .delete-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 20px;
        height: 20px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 10px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .placed-placeholder:hover .delete-btn {
        display: flex;
    }

    /* Resize Handle */
    .placed-placeholder .resize-handle {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        background: #667eea;
        cursor: se-resize;
        border-radius: 0 0 4px 0;
    }

    /* Placed Fields List */
    .placed-fields-list .placed-field-item {
        padding: 8px 10px;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 6px;
        font-size: 12px;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .placed-field-item .field-name {
        flex: 1;
    }

    .placed-field-item .page-badge {
        font-size: 10px;
        background: #667eea;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
    }

    /* Toolbar */
    .toolbar-card {
        background: #f8f9fa;
    }

    /* Cards */
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 8px;
    }

    /* Drop Zone Active */
    .pdf-page-wrapper.drop-active {
        outline: 3px dashed #667eea;
        outline-offset: -3px;
    }
</style>
@endpush

@push('scripts')
<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Set PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

$(document).ready(function() {
    // Template data
    const templateId = {{ $template->id }};
    const pdfUrl = "{{ $template->pdf_path ? asset('storage/' . $template->pdf_path) : asset('storage/' . $template->document_path) }}";
    
    let pdfDoc = null;
    let currentPage = 1;
    let totalPages = 0;
    let scale = 1.0;
    let placedFields = @json($template->placeholders ?? []);
    let selectedField = null;
    let fieldIdCounter = placedFields.length;

    // Initialize
    loadPDF();
    updatePlacedFieldsList();

    // Load PDF
    async function loadPDF() {
        try {
            pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
            totalPages = pdfDoc.numPages;
            $('#totalPages').text(totalPages);
            
            // Render all pages
            await renderAllPages();
            
            // Restore placed fields
            restorePlacedFields();
            
        } catch (error) {
            console.error('Error loading PDF:', error);
            showNotification('Error loading document. Please try again.', 'error');
        }
    }

    // Render all pages
    async function renderAllPages() {
        const viewer = $('#pdfViewer');
        viewer.empty();

        for (let i = 1; i <= totalPages; i++) {
            const page = await pdfDoc.getPage(i);
            const viewport = page.getViewport({ scale: scale });
            
            // Create page wrapper
            const pageWrapper = $(`<div class="pdf-page-wrapper" data-page="${i}"></div>`);
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            pageWrapper.append(canvas);
            viewer.append(pageWrapper);

            // Render page
            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            // Setup drop zone for this page
            setupPageDropZone(pageWrapper, i);
        }
    }

    // Setup drop zone for a page
    function setupPageDropZone(pageWrapper, pageNum) {
        pageWrapper.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drop-active');
        });

        pageWrapper.on('dragleave', function(e) {
            $(this).removeClass('drop-active');
        });

        pageWrapper.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drop-active');

            const data = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
            const rect = this.getBoundingClientRect();
            const x = e.originalEvent.clientX - rect.left;
            const y = e.originalEvent.clientY - rect.top;

            // Create placeholder at drop position
            createPlaceholder(data, pageNum, x, y, pageWrapper);
        });
    }

    // Draggable fields from sidebar
    $('.draggable-field').on('dragstart', function(e) {
        const field = $(this).data('field');
        const label = $(this).data('label');
        const type = $(this).data('type');

        e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify({
            field: field,
            label: label,
            type: type
        }));
    });

    // Create placeholder on page
    function createPlaceholder(data, pageNum, x, y, pageWrapper) {
        fieldIdCounter++;
        const fieldId = `field_${fieldIdCounter}`;
        const isSignature = data.type === 'signature';
        
        const width = isSignature ? 180 : 120;
        const height = isSignature ? 60 : 26;

        const placeholder = $(`
            <div class="placed-placeholder ${isSignature ? 'signature' : ''}" 
                 id="${fieldId}"
                 data-field="${data.field}"
                 data-label="${data.label}"
                 data-type="${data.type}"
                 data-page="${pageNum}"
                 style="left: ${x}px; top: ${y}px; width: ${width}px; height: ${height}px;">
                <span class="field-label">${data.label}</span>
                <button class="delete-btn" title="Remove"><i class="fas fa-times"></i></button>
                <div class="resize-handle"></div>
            </div>
        `);

        // Append to page wrapper (positioned relative to page)
        pageWrapper.append(placeholder);

        // Make it draggable within the page
        makeDraggable(placeholder, pageWrapper);

        // Make it resizable
        makeResizable(placeholder);

        // Delete button
        placeholder.find('.delete-btn').on('click', function(e) {
            e.stopPropagation();
            placeholder.remove();
            updatePlacedFieldsList();
        });

        // Double-click to edit properties
        placeholder.on('dblclick', function() {
            openFieldProperties($(this));
        });

        // Add to placed fields
        placedFields.push({
            id: fieldId,
            field: data.field,
            label: data.label,
            type: data.type,
            page: pageNum,
            x: x,
            y: y,
            width: width,
            height: height,
            fontSize: 12
        });

        updatePlacedFieldsList();
    }

    // Make placeholder draggable
    function makeDraggable(element, container) {
        let isDragging = false;
        let startX, startY, origX, origY;

        element.on('mousedown', function(e) {
            if ($(e.target).hasClass('resize-handle') || $(e.target).hasClass('delete-btn')) return;
            
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            origX = parseInt(element.css('left'));
            origY = parseInt(element.css('top'));
            
            element.addClass('active');
            selectedField = element;
        });

        $(document).on('mousemove', function(e) {
            if (!isDragging) return;

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

            let newX = origX + dx;
            let newY = origY + dy;

            // Bounds checking
            const containerWidth = container.width();
            const containerHeight = container.height();
            const elemWidth = element.outerWidth();
            const elemHeight = element.outerHeight();

            newX = Math.max(0, Math.min(newX, containerWidth - elemWidth));
            newY = Math.max(0, Math.min(newY, containerHeight - elemHeight));

            element.css({ left: newX, top: newY });
        });

        $(document).on('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                element.removeClass('active');
                updateFieldPosition(element);
            }
        });
    }

    // Make placeholder resizable
    function makeResizable(element) {
        const handle = element.find('.resize-handle');
        let isResizing = false;
        let startX, startY, origW, origH;

        handle.on('mousedown', function(e) {
            e.stopPropagation();
            isResizing = true;
            startX = e.clientX;
            startY = e.clientY;
            origW = element.width();
            origH = element.height();
        });

        $(document).on('mousemove', function(e) {
            if (!isResizing) return;

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

            element.css({
                width: Math.max(60, origW + dx),
                height: Math.max(20, origH + dy)
            });
        });

        $(document).on('mouseup', function() {
            if (isResizing) {
                isResizing = false;
                updateFieldPosition(element);
            }
        });
    }

    // Update field position in data
    function updateFieldPosition(element) {
        const fieldId = element.attr('id');
        const field = placedFields.find(f => f.id === fieldId);
        if (field) {
            field.x = parseInt(element.css('left'));
            field.y = parseInt(element.css('top'));
            field.width = element.width();
            field.height = element.height();
        }
    }

    // Open field properties modal
    function openFieldProperties(element) {
        selectedField = element;
        const fieldId = element.attr('id');
        const field = placedFields.find(f => f.id === fieldId);

        if (field) {
            $('#fieldLabel').val(field.label);
            $('#fieldWidth').val(field.width);
            $('#fieldHeight').val(field.height);
            $('#fieldFontSize').val(field.fontSize || 12);
            $('#fieldPropertiesModal').modal('show');
        }
    }

    // Apply field properties
    $('#applyFieldBtn').on('click', function() {
        if (!selectedField) return;

        const width = parseInt($('#fieldWidth').val());
        const height = parseInt($('#fieldHeight').val());
        const fontSize = parseInt($('#fieldFontSize').val());

        selectedField.css({ width, height });
        
        const fieldId = selectedField.attr('id');
        const field = placedFields.find(f => f.id === fieldId);
        if (field) {
            field.width = width;
            field.height = height;
            field.fontSize = fontSize;
        }

        $('#fieldPropertiesModal').modal('hide');
    });

    // Delete field from modal
    $('#deleteFieldBtn').on('click', function() {
        if (!selectedField) return;
        
        const fieldId = selectedField.attr('id');
        placedFields = placedFields.filter(f => f.id !== fieldId);
        selectedField.remove();
        selectedField = null;
        
        $('#fieldPropertiesModal').modal('hide');
        updatePlacedFieldsList();
    });

    // Update placed fields list
    function updatePlacedFieldsList() {
        const list = $('#placedFieldsList');
        
        if (placedFields.length === 0) {
            list.html('<p class="text-muted small text-center py-3">No fields placed yet</p>');
            return;
        }

        let html = '';
        placedFields.forEach(field => {
            html += `
                <div class="placed-field-item" data-id="${field.id}">
                    <span class="field-name">${field.label}</span>
                    <span class="page-badge">P${field.page}</span>
                </div>
            `;
        });
        list.html(html);

        // Click to highlight field
        list.find('.placed-field-item').on('click', function() {
            const id = $(this).data('id');
            $(`#${id}`).addClass('active');
            setTimeout(() => $(`#${id}`).removeClass('active'), 2000);
        });
    }

    // Restore placed fields from saved data
    function restorePlacedFields() {
        placedFields.forEach(field => {
            const pageWrapper = $(`.pdf-page-wrapper[data-page="${field.page}"]`);
            if (!pageWrapper.length) return;

            const isSignature = field.type === 'signature';
            
            const placeholder = $(`
                <div class="placed-placeholder ${isSignature ? 'signature' : ''}" 
                     id="${field.id}"
                     data-field="${field.field}"
                     data-label="${field.label}"
                     data-type="${field.type}"
                     data-page="${field.page}"
                     style="left: ${field.x}px; top: ${field.y}px; width: ${field.width}px; height: ${field.height}px;">
                    <span class="field-label">${field.label}</span>
                    <button class="delete-btn" title="Remove"><i class="fas fa-times"></i></button>
                    <div class="resize-handle"></div>
                </div>
            `);

            pageWrapper.append(placeholder);
            makeDraggable(placeholder, pageWrapper);
            makeResizable(placeholder);

            placeholder.find('.delete-btn').on('click', function(e) {
                e.stopPropagation();
                const id = placeholder.attr('id');
                placedFields = placedFields.filter(f => f.id !== id);
                placeholder.remove();
                updatePlacedFieldsList();
            });

            placeholder.on('dblclick', function() {
                openFieldProperties($(this));
            });
        });

        updatePlacedFieldsList();
    }

    // Zoom controls
    $('#zoomIn').on('click', async function() {
        if (scale < 2.0) {
            scale += 0.25;
            await renderAllPages();
            restorePlacedFields();
            $('#zoomLevel').text(Math.round(scale * 100) + '%');
        }
    });

    $('#zoomOut').on('click', async function() {
        if (scale > 0.5) {
            scale -= 0.25;
            await renderAllPages();
            restorePlacedFields();
            $('#zoomLevel').text(Math.round(scale * 100) + '%');
        }
    });

    // Page navigation
    $('#prevPage').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            scrollToPage(currentPage);
        }
    });

    $('#nextPage').on('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            scrollToPage(currentPage);
        }
    });

    function scrollToPage(pageNum) {
        const pageWrapper = $(`.pdf-page-wrapper[data-page="${pageNum}"]`);
        if (pageWrapper.length) {
            pageWrapper[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            $('#currentPage').text(pageNum);
        }
    }

    // Save template
    $('#saveTemplateBtn').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '{{ route("lease-templates.update", $template->id) }}',
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                placeholders: JSON.stringify(placedFields),
                content: '',
                signatures: JSON.stringify(placedFields.filter(f => f.type === 'signature'))
            },
            success: function(response) {
                btn.html('<i class="fas fa-check"></i> Saved!');
                setTimeout(() => {
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }, 2000);
                showNotification('Template saved successfully!', 'success');
            },
            error: function(xhr) {
                btn.html(originalText);
                btn.prop('disabled', false);
                showNotification('Error saving template.', 'error');
            }
        });
    });

    // Notification helper
    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);
        setTimeout(() => alert.alert('close'), 3000);
    }
});
</script>
@endpush
