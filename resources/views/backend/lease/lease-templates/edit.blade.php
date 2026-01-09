@extends('backend.app')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app mb-3">
        <!-- CONTAINER -->
        <div class="main-container container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Edit Lease Template: {{ $template->name }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Lease Template</li>
                    </ol>
                </div>
                <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Templates
                </a>
            </div>
            <!-- PAGE-HEADER END -->
                        
            <!-- Main Editor Layout -->
            <div class="row mt-3">
                <!-- Left Sidebar - Placeholder Palette -->
                <div class="col-md-3">
                    <div class="card placeholder-palette sticky-top" style="top: 100px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-cubes"></i> Placeholders</h5>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle"></i> Drag placeholders to the document
                            </p>

                            <!-- Tenant Information -->
                            <div class="palette-group mb-3">
                                <h6 class="palette-group-title">
                                    <i class="fas fa-user"></i> Tenant Information
                                </h6>
                                <div class="palette-items">
                                    <div class="palette-item" draggable="true" data-field="tenant_name" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Tenant Name</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="tenant_email" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Tenant Email</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="tenant_phone" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Tenant Phone</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Details -->
                            <div class="palette-group mb-3">
                                <h6 class="palette-group-title">
                                    <i class="fas fa-building"></i> Property Details
                                </h6>
                                <div class="palette-items">
                                    <div class="palette-item" draggable="true" data-field="property_address" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Property Address</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Lease Terms -->
                            <div class="palette-group mb-3">
                                <h6 class="palette-group-title">
                                    <i class="fas fa-file-contract"></i> Lease Terms
                                </h6>
                                <div class="palette-items">
                                    <div class="palette-item" draggable="true" data-field="lease_start_date" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Start Date</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="lease_end_date" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>End Date</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="lease_term" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Lease Term</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="monthly_rent" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Monthly Rent</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="security_deposit" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Security Deposit</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin/Landlord -->
                            <div class="palette-group mb-3">
                                <h6 class="palette-group-title">
                                    <i class="fas fa-user-tie"></i> Landlord Info
                                </h6>
                                <div class="palette-items">
                                    <div class="palette-item" draggable="true" data-field="admin_name" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Landlord Name</span>
                                    </div>
                                    <div class="palette-item" draggable="true" data-field="admin_email" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Landlord Email</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Signatures -->
                            <div class="palette-group mb-3">
                                <h6 class="palette-group-title">
                                    <i class="fas fa-signature"></i> Signatures
                                </h6>
                                <div class="palette-items">
                                    <div class="palette-item palette-item-signature admin" draggable="true" data-field="admin_signature" data-type="signature">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Admin Signature</span>
                                    </div>
                                    <div class="palette-item palette-item-signature tenant" draggable="true" data-field="tenant_signature" data-type="signature">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Tenant Signature</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Other -->
                            <div class="palette-group">
                                <h6 class="palette-group-title">
                                    <i class="fas fa-calendar"></i> Other
                                </h6>
                                <div class="palette-items">
                                    <div class="palette-item" draggable="true" data-field="current_date" data-type="field">
                                        <i class="fas fa-grip-vertical"></i>
                                        <span>Current Date</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Document Editor -->
                <div class="col-md-9">
                    <!-- Toolbar -->
                    <div class="card mb-3">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-secondary me-2">{{ $template->file_type }}</span>
                                    <span class="text-muted">{{ $template->name }}</span>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="previewTemplateBtn">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <button type="button" class="btn btn-success" id="saveTemplateBtn">
                                        <i class="fas fa-save"></i> Save Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="alert alert-info mb-3 py-2">
                        <i class="fas fa-info-circle"></i> 
                        <strong>How to use:</strong> 
                        Drag placeholders from the left panel and drop them into the document. 
                        Placeholders in the document can be dragged to reposition or clicked to remove.
                    </div>

                    <!-- Document Editor Container -->
                    <div class="card">
                        <div class="card-body document-editor-container">
                            <div id="templateEditor" contenteditable="true" class="document-editor">
                                {!! $template->content !!}
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
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white no-print">
                <h5 class="modal-title"><i class="fas fa-file-pdf"></i> Print/PDF Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent" style="background: #525659; padding: 30px; overflow-y: auto;">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
    window.templateData = @json($template);
</script>
<script src="{{ asset('js/lease_template_editor.js') }}"></script>
@endpush

@push('styles')
<style>
    /* Palette Styles */
    .placeholder-palette {
        max-height: calc(100vh - 150px);
        overflow-y: auto;
    }

    .palette-group-title {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }

    .palette-group-title i {
        margin-right: 5px;
        width: 16px;
    }

    .palette-item {
        background: #fff3cd;
        border: 2px dashed #ffc107;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 6px;
        cursor: grab;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #856404;
        transition: all 0.2s ease;
        user-select: none;
    }

    .palette-item:hover {
        background: #ffe69c;
        transform: translateX(3px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .palette-item:active, .palette-item.dragging {
        cursor: grabbing;
        opacity: 0.6;
        transform: scale(0.95);
    }

    .palette-item i.fa-grip-vertical {
        color: #b8860b;
        opacity: 0.6;
    }

    .palette-item-signature {
        background: #d4edda;
        border-color: #28a745;
        color: #155724;
    }

    .palette-item-signature:hover {
        background: #c3e6cb;
    }

    .palette-item-signature i.fa-grip-vertical {
        color: #155724;
    }

    .palette-item-signature.tenant {
        background: #d1ecf1;
        border-color: #17a2b8;
        color: #0c5460;
    }

    .palette-item-signature.tenant:hover {
        background: #bee5eb;
    }

    .palette-item-signature.tenant i.fa-grip-vertical {
        color: #0c5460;
    }

    /* Document Editor Styles */
    .document-editor-container {
        background: #e0e0e0;
        padding: 30px;
        min-height: 800px;
    }

    .document-editor {
        background: white;
        padding: 20px 10px;
        min-height: 1100px;
        box-shadow: 0 0 20px rgba(0,0,0,0.15);
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        max-width: 100%;
        margin: 0 auto;
        border: 1px solid #ccc;
        cursor: text;
    }

    .document-editor.drop-active {
        outline: 3px dashed #007bff;
        outline-offset: -10px;
        background: #f0f7ff;
    }

    /* Placeholder in document */
    .document-editor .placeholder {
        background-color: #fff3cd;
        border: 2px dashed #ffc107;
        padding: 2px 10px;
        border-radius: 4px;
        display: inline-block;
        cursor: grab;
        font-weight: 600;
        color: #856404;
        font-family: monospace;
        font-size: 11px;
        transition: all 0.2s;
        user-select: none;
    }

    .document-editor .placeholder:hover {
        background-color: #ffe69c;
        border-color: #e0a800;
        transform: scale(1.02);
    }

    .document-editor .placeholder:active,
    .document-editor .placeholder.dragging {
        cursor: grabbing;
        opacity: 0.6;
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Signature Placeholder in document */
    .document-editor .signature-placeholder {
        background-color: #d4edda;
        border: 2px dashed #28a745;
        padding: 2px 10px;
        border-radius: 4px;
        display: inline-block;
        cursor: grab;
        font-weight: 600;
        color: #155724;
        font-family: monospace;
        font-size: 11px;
        transition: all 0.2s;
        user-select: none;
    }

    .document-editor .signature-placeholder.admin-signature {
        background-color: #d4edda;
        border-color: #28a745;
        color: #155724;
    }

    .document-editor .signature-placeholder.tenant-signature {
        background-color: #d1ecf1;
        border-color: #17a2b8;
        color: #0c5460;
    }

    .document-editor .signature-placeholder:hover {
        transform: scale(1.02);
    }

    .document-editor .signature-placeholder:active,
    .document-editor .signature-placeholder.dragging {
        cursor: grabbing;
        opacity: 0.6;
    }

    /* Drop Indicator */
    .drop-indicator {
        display: inline-block;
        width: 3px;
        height: 20px;
        background-color: #007bff;
        margin: 0 2px;
        animation: blink 0.5s infinite;
        vertical-align: middle;
        border-radius: 2px;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Document content styles */
    .document-editor p {
        margin-bottom: 12px;
    }

    .document-editor h1,
    .document-editor h2,
    .document-editor h3 {
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .document-editor table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }

    .document-editor td,
    .document-editor th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    /* Print Styles */
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
        }
        .no-print { display: none !important; }
        .modal {
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
            overflow: visible !important;
        }
        .modal-dialog {
            margin: 0;
            max-width: 100%;
        }
        .modal-content {
            border: none;
        }
        .modal-body {
            padding: 0 !important;
            background: white !important;
        }
    }

    /* Preview Styles */
    .preview-document {
        background: white;
        padding: 20px 10px;
        max-width: 100%;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.15);
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
    }
</style>
@endpush
