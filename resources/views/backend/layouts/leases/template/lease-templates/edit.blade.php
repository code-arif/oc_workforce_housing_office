@extends('backend.app')
@section('title', 'Edit Lease Template: ' . $template->name)
@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Edit Lease Template</h1>
                    <p class="text-muted mb-0">{{ $template->name }}</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <!-- PAGE HEADER END -->

            <div class="row">
                <!-- Left Sidebar - Placeholder Palette -->
                <div class="col-xl-3 col-lg-4">
                    <div class="card sticky-sidebar">
                        <div class="card-header bg-gradient-primary">
                            <h5 class="card-title text-white mb-0">
                                <i class="fas fa-cubes"></i> Placeholder Library
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Search Placeholders -->
                            <div class="placeholder-search p-3 border-bottom">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="searchPlaceholders" 
                                       placeholder="Search placeholders...">
                            </div>

                            <!-- Placeholder Groups -->
                            <div class="placeholder-groups">
                                <!-- Tenant Information -->
                                <div class="placeholder-group">
                                    <div class="group-header" data-bs-toggle="collapse" data-bs-target="#tenantGroup">
                                        <i class="fas fa-user"></i>
                                        <span>Tenant Information</span>
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="tenantGroup">
                                        <div class="group-items">
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="tenant_name" 
                                                 data-label="Tenant Name"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Tenant Name</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="tenant_email" 
                                                 data-label="Tenant Email"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Tenant Email</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="tenant_phone" 
                                                 data-label="Tenant Phone"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Tenant Phone</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="tenant_address" 
                                                 data-label="Tenant Address"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Tenant Address</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Details -->
                                <div class="placeholder-group">
                                    <div class="group-header" data-bs-toggle="collapse" data-bs-target="#propertyGroup">
                                        <i class="fas fa-building"></i>
                                        <span>Property Details</span>
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="propertyGroup">
                                        <div class="group-items">
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="property_address" 
                                                 data-label="Property Address"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Property Address</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="property_type" 
                                                 data-label="Property Type"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Property Type</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="room_number" 
                                                 data-label="Room Number"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Room Number</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lease Terms -->
                                <div class="placeholder-group">
                                    <div class="group-header" data-bs-toggle="collapse" data-bs-target="#leaseGroup">
                                        <i class="fas fa-file-contract"></i>
                                        <span>Lease Terms</span>
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="leaseGroup">
                                        <div class="group-items">
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="lease_start_date" 
                                                 data-label="Start Date"
                                                 data-type="date">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Start Date</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="lease_end_date" 
                                                 data-label="End Date"
                                                 data-type="date">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">End Date</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="lease_term" 
                                                 data-label="Lease Term"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Lease Term</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="monthly_rent" 
                                                 data-label="Monthly Rent"
                                                 data-type="currency">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Monthly Rent</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="security_deposit" 
                                                 data-label="Security Deposit"
                                                 data-type="currency">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Security Deposit</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Landlord/Admin Information -->
                                <div class="placeholder-group">
                                    <div class="group-header" data-bs-toggle="collapse" data-bs-target="#landlordGroup">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Landlord Information</span>
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="landlordGroup">
                                        <div class="group-items">
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="landlord_name" 
                                                 data-label="Landlord Name"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Landlord Name</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="landlord_email" 
                                                 data-label="Landlord Email"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Landlord Email</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="landlord_phone" 
                                                 data-label="Landlord Phone"
                                                 data-type="text">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Landlord Phone</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Signatures -->
                                <div class="placeholder-group">
                                    <div class="group-header" data-bs-toggle="collapse" data-bs-target="#signatureGroup">
                                        <i class="fas fa-signature"></i>
                                        <span>Signatures</span>
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="signatureGroup">
                                        <div class="group-items">
                                            <div class="placeholder-item signature-item" draggable="true" 
                                                 data-field="landlord_signature" 
                                                 data-label="Landlord Signature"
                                                 data-type="signature">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Landlord Signature</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                            <div class="placeholder-item signature-item" draggable="true" 
                                                 data-field="tenant_signature" 
                                                 data-label="Tenant Signature"
                                                 data-type="signature">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Tenant Signature</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Other Fields -->
                                <div class="placeholder-group">
                                    <div class="group-header" data-bs-toggle="collapse" data-bs-target="#otherGroup">
                                        <i class="fas fa-ellipsis-h"></i>
                                        <span>Other</span>
                                        <i class="fas fa-chevron-down ms-auto"></i>
                                    </div>
                                    <div class="collapse show" id="otherGroup">
                                        <div class="group-items">
                                            <div class="placeholder-item" draggable="true" 
                                                 data-field="current_date" 
                                                 data-label="Current Date"
                                                 data-type="date">
                                                <i class="fas fa-grip-vertical handle"></i>
                                                <span class="item-label">Current Date</span>
                                                <i class="fas fa-plus-circle add-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="mb-3">Template Statistics</h6>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-value">{{ $template->total_pages }}</div>
                                    <div class="stat-label">Pages</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value" id="placeholderCount">0</div>
                                    <div class="stat-label">Placeholders</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value" id="signatureCount">0</div>
                                    <div class="stat-label">Signatures</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Document Editor -->
                <div class="col-xl-9 col-lg-8">
                    <!-- Toolbar -->
                    <div class="card editor-toolbar">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="toolbar-left">
                                    <span class="badge bg-primary me-2">{{ strtoupper($template->file_type) }}</span>
                                    <span class="text-muted">{{ $template->original_filename }}</span>
                                </div>
                                <div class="toolbar-right">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOut">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomReset">
                                            <span id="zoomLevel">100%</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomIn">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-info ms-2" id="previewBtn">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success ms-2" id="saveBtn">
                                        <i class="fas fa-save"></i> Save Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions Alert -->
                    <div class="alert alert-info d-flex align-items-center" id="instructionsAlert">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>How to add placeholders:</strong> Drag placeholders from the left panel and drop them into the document at the desired location. Click on a placeholder to remove it.
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>

                    <!-- Page Navigation -->
                    @if($template->total_pages > 1)
                    <div class="card page-navigation-card">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted">Page:</span>
                                    <strong id="currentPageDisplay">1</strong> of <strong>{{ $template->total_pages }}</strong>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="prevPage" disabled>
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="nextPage">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Document Editor -->
                    <div class="card document-card">
                        <div class="card-body p-0">
                            <div class="document-wrapper">
                                @if($template->pages && count($template->pages) > 0)
                                    @foreach($template->pages as $page)
                                    <div class="document-page" 
                                         data-page="{{ $page['page_number'] }}" 
                                         style="{{ $page['page_number'] > 1 ? 'display: none;' : '' }}">
                                        <div class="page-header-indicator">
                                            <span class="page-number-badge">Page {{ $page['page_number'] }}</span>
                                        </div>
                                        <div class="document-editor" contenteditable="true">
                                            {!! $page['content'] !!}
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="document-page" data-page="1">
                                        <div class="document-editor" contenteditable="true">
                                            {!! $template->content !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Template Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent" class="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
    }

    /* Sticky Sidebar */
    .sticky-sidebar {
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }

    /* Card Styling */
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .bg-gradient-primary {
        background: var(--primary-gradient);
    }

    /* Placeholder Groups */
    .placeholder-groups {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
    }

    .placeholder-group {
        border-bottom: 1px solid #e5e7eb;
    }

    .group-header {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .group-header:hover {
        background: #f9fafb;
    }

    .group-header i.fa-chevron-down {
        font-size: 12px;
        transition: transform 0.3s;
    }

    .group-header[aria-expanded="false"] i.fa-chevron-down {
        transform: rotate(-90deg);
    }

    .group-items {
        padding: 8px 0;
    }

    .placeholder-item {
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: move;
        transition: all 0.2s;
        border-left: 3px solid transparent;
        font-size: 13px;
    }

    .placeholder-item:hover {
        background: #f0f9ff;
        border-left-color: #3b82f6;
    }

    .placeholder-item.signature-item:hover {
        background: #fef3c7;
        border-left-color: #f59e0b;
    }

    .placeholder-item .handle {
        color: #9ca3af;
        font-size: 12px;
    }

    .placeholder-item .item-label {
        flex: 1;
        color: #374151;
    }

    .placeholder-item .add-icon {
        color: #10b981;
        font-size: 14px;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .placeholder-item:hover .add-icon {
        opacity: 1;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 500;
    }

    /* Page Navigation */
    .page-navigation-card {
        margin-bottom: 15px;
    }

    .page-number-badge {
        display: inline-block;
        background: #667eea;
        color: white;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .document-page {
        position: relative;
    }

    .page-header-indicator {
        text-align: right;
        padding: 10px 20px;
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    /* Editor Toolbar */
    .editor-toolbar {
        margin-bottom: 15px;
    }

    .editor-toolbar .card-body {
        background: #f9fafb;
    }

    /* Document Card */
    .document-card {
        min-height: 800px;
    }

    .document-wrapper {
        background: #e5e7eb;
        padding: 30px;
        min-height: 800px;
    }

    .document-editor {
        background: white;
        padding: 60px 80px;
        min-height: 1100px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        font-family: 'Times New Roman', serif;
        font-size: 14px;
        line-height: 1.6;
        color: #1f2937;
        outline: none;
    }

    .document-editor:focus {
        outline: 2px solid #667eea;
    }

    /* Placeholder in Document */
    .document-editor .placeholder {
        display: inline-block;
        padding: 4px 12px;
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        border: 2px dashed #667eea;
        border-radius: 6px;
        color: #667eea;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        margin: 0 2px;
    }

    .document-editor .placeholder:hover {
        background: #667eea;
        color: white;
        border-style: solid;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .document-editor .placeholder:hover:after {
        content: '×';
        position: absolute;
        right: -8px;
        top: -8px;
        background: #ef4444;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }

    .document-editor .signature-placeholder {
        display: block;
        width: 300px;
        height: 80px;
        background: linear-gradient(135deg, #f59e0b15 0%, #d97706  15 100%);
        border: 2px dashed #f59e0b;
        border-radius: 8px;
        text-align: center;
        padding: 20px;
        color: #f59e0b;
        font-weight: 600;
        cursor: pointer;
        margin: 20px 0;
    }

    .document-editor .signature-placeholder:hover {
        background: #f59e0b;
        color: white;
        border-style: solid;
    }

    /* Drag Over State */
    .document-editor.drag-over {
        outline: 3px dashed #667eea;
        background: #f0f9ff;
    }

    /* Preview Content */
    .preview-content {
        background: white;
        padding: 60px 80px;
        font-family: 'Times New Roman', serif;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Zoom Controls */
    #zoomLevel {
        min-width: 50px;
        display: inline-block;
        font-size: 13px;
    }

    /* Search */
    .placeholder-search input {
        border-radius: 8px;
        font-size: 13px;
    }

    /* Alert */
    .alert {
        border: none;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentZoom = 100;
    let currentPage = 1;
    let totalPages = {{ $template->total_pages ?? 1 }};
    let placeholders = @json($template->placeholders ?? []);
    let signatures = @json($template->signatures ?? []);
    let pageContents = {}; // Store content for each page

    // Initialize
    updateStats();
    initializePageNavigation();

    // Page Navigation
    function initializePageNavigation() {
        if (totalPages <= 1) return;

        updatePageButtons();

        $('#prevPage').on('click', function() {
            if (currentPage > 1) {
                saveCurrentPage();
                currentPage--;
                showPage(currentPage);
            }
        });

        $('#nextPage').on('click', function() {
            if (currentPage < totalPages) {
                saveCurrentPage();
                currentPage++;
                showPage(currentPage);
            }
        });
    }

    function showPage(pageNumber) {
        $('.document-page').hide();
        $(`.document-page[data-page="${pageNumber}"]`).show();
        $('#currentPageDisplay').text(pageNumber);
        updatePageButtons();
        attachPlaceholderEvents();
    }

    function saveCurrentPage() {
        const pageElement = $(`.document-page[data-page="${currentPage}"]`);
        const content = pageElement.find('.document-editor').html();
        pageContents[currentPage] = content;
    }

    function updatePageButtons() {
        $('#prevPage').prop('disabled', currentPage <= 1);
        $('#nextPage').prop('disabled', currentPage >= totalPages);
    }

    // Drag and Drop from Palette
    $('.placeholder-item').on('dragstart', function(e) {
        const field = $(this).data('field');
        const label = $(this).data('label');
        const type = $(this).data('type');

        e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify({
            field: field,
            label: label,
            type: type
        }));
    });

    // Document editor drag events - apply to all pages
    $('.document-editor').each(function() {
        const editor = $(this);

        editor.on('dragover', function(e) {
            e.preventDefault();
            $(this).closest('.document-page').addClass('drag-over');
        });

        editor.on('dragleave', function(e) {
            $(this).closest('.document-page').removeClass('drag-over');
        });

        editor.on('drop', function(e) {
            e.preventDefault();
            $(this).closest('.document-page').removeClass('drag-over');

            const data = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
            
            // Insert placeholder at drop position
            insertPlaceholder(data, e.originalEvent, this);
        });
    });

    // Insert placeholder into document
    function insertPlaceholder(data, event, editorElement) {
        const range = document.caretRangeFromPoint(event.clientX, event.clientY);
        
        if (!range) return;

        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        let placeholderHtml;
        if (data.type === 'signature') {
            placeholderHtml = `<div class="signature-placeholder" contenteditable="false" data-field="${data.field}" data-type="signature">${data.label}</div>`;
        } else {
            placeholderHtml = `<span class="placeholder" contenteditable="false" data-field="${data.field}" data-type="${data.type}">${data.label}</span>`;
        }

        // Insert at cursor position
        document.execCommand('insertHTML', false, placeholderHtml);

        // Add to placeholders array
        if (data.type === 'signature') {
            signatures.push({
                field: data.field,
                label: data.label,
                type: data.type
            });
        } else {
            placeholders.push({
                field: data.field,
                label: data.label,
                type: data.type
            });
        }

        updateStats();
        attachPlaceholderEvents();
    }

    // Remove placeholder on click
    function attachPlaceholderEvents() {
        $('.placeholder, .signature-placeholder').off('click').on('click', function() {
            if (confirm('Remove this placeholder?')) {
                const field = $(this).data('field');
                $(this).remove();

                // Remove from arrays
                placeholders = placeholders.filter(p => p.field !== field);
                signatures = signatures.filter(s => s.field !== field);

                updateStats();
            }
        });
    }

    // Initial attachment
    attachPlaceholderEvents();

    // Update statistics
    function updateStats() {
        $('#placeholderCount').text(placeholders.length);
        $('#signatureCount').text(signatures.length);
    }

    // Zoom controls
    $('#zoomIn').on('click', function() {
        if (currentZoom < 150) {
            currentZoom += 10;
            applyZoom();
        }
    });

    $('#zoomOut').on('click', function() {
        if (currentZoom > 50) {
            currentZoom -= 10;
            applyZoom();
        }
    });

    $('#zoomReset').on('click', function() {
        currentZoom = 100;
        applyZoom();
    });

    function applyZoom() {
        $('.document-editor').css('transform', `scale(${currentZoom / 100})`);
        $('.document-editor').css('transform-origin', 'top center');
        $('#zoomLevel').text(currentZoom + '%');
    }

    // Search placeholders
    $('#searchPlaceholders').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();

        $('.placeholder-item').each(function() {
            const label = $(this).data('label').toLowerCase();
            if (label.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Hide empty groups
        $('.placeholder-group').each(function() {
            const visibleItems = $(this).find('.placeholder-item:visible').length;
            if (visibleItems === 0) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });

    // Preview
    $('#previewBtn').on('click', function() {
        saveCurrentPage();
        
        // Combine all pages for preview
        let fullContent = '';
        $('.document-page').each(function() {
            const pageNum = $(this).data('page');
            const content = pageContents[pageNum] || $(this).find('.document-editor').html();
            fullContent += `<div class="preview-page"><h6>Page ${pageNum}</h6>${content}<hr></div>`;
        });
        
        $('#previewContent').html(fullContent);
        $('#previewModal').modal('show');
    });

    // Save template
    $('#saveBtn').on('click', function() {
        saveCurrentPage(); // Save current page before saving all
        
        // Collect all page contents
        let pagesData = [];
        $('.document-page').each(function() {
            const pageNum = $(this).data('page');
            const content = pageContents[pageNum] || $(this).find('.document-editor').html();
            pagesData.push({
                page_number: pageNum,
                content: content,
                format: 'html'
            });
        });
        
        // Combine all pages for main content
        const fullContent = pagesData.map(p => p.content).join('\n');
        
        const btn = $(this);
        const originalText = btn.html();

        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '{{ route("lease-templates.update", $template->id) }}',
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                content: fullContent,
                pages: JSON.stringify(pagesData),
                total_pages: totalPages,
                placeholders: JSON.stringify(placeholders),
                signatures: JSON.stringify(signatures)
            },
            success: function(response) {
                btn.html('<i class="fas fa-check"></i> Saved!');
                setTimeout(() => {
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }, 2000);

                // Show success message
                showNotification('Template saved successfully!', 'success');
            },
            error: function(xhr) {
                btn.html(originalText);
                btn.prop('disabled', false);
                showNotification('Error saving template. Please try again.', 'error');
            }
        });
    });

    // Notification helper
    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
});
</script>
@endpush
