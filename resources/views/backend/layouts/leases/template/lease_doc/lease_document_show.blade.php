@extends('backend.app')
@section('title', 'Lease Document #' .{{ @$document->id }})
@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Lease Document #{{ @$document->id }}</h2>
                        <div>
                            <a href="{{ route('lease-documents.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Documents
                            </a>
                            <span class="badge bg-{{ $document->status === 'signed' ? 'success' : ($document->status === 'pending_signatures' ? 'warning' : 'secondary') }} ms-2">
                                {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <!-- Document Preview -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Document Content</h5>
                            <span class="badge bg-info">{{ $document->leaseTemplate->name ?? 'N/A' }}</span>
                        </div>
                        <div class="card-body document-preview-container">
                            <div id="documentContent" class="document-preview">
                                {!! $document->rendered_content !!}
                            </div>

                            <!-- Signature Areas -->
                            @if($document->leaseTemplate && $document->leaseTemplate->signatures)
                                <div class="signatures-section mt-4 pt-4 border-top">
                                    <h5 class="mb-4">Signatures</h5>
                                    <div class="row">
                                        @foreach($document->leaseTemplate->signatures as $signature)
                                            @if($signature['type'] === 'admin')
                                                <div class="col-md-6">
                                                    <div class="signature-area" id="adminSignatureArea">
                                                        <h6><i class="fas fa-user-tie"></i> Admin/Landlord Signature</h6>
                                                        @if($document->admin_signature)
                                                            <div class="signature-display text-center p-3 bg-light rounded">
                                                                <img src="{{ $document->admin_signature }}" alt="Admin Signature" style="max-width: 250px; border-bottom: 2px solid #000;">
                                                                <p class="text-muted mb-0 mt-2"><small>Signed on: {{ $document->admin_signed_at->format('F d, Y H:i') }}</small></p>
                                                            </div>
                                                        @else
                                                            <button class="btn btn-success btn-lg" id="signAdminBtn">
                                                                <i class="fas fa-pen"></i> Sign as Admin
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            @if($signature['type'] === 'tenant')
                                                <div class="col-md-6">
                                                    <div class="signature-area" id="tenantSignatureArea">
                                                        <h6><i class="fas fa-user"></i> Tenant Signature</h6>
                                                        @if($document->tenant_signature)
                                                            <div class="signature-display text-center p-3 bg-light rounded">
                                                                <img src="{{ $document->tenant_signature }}" alt="Tenant Signature" style="max-width: 250px; border-bottom: 2px solid #000;">
                                                                <p class="text-muted mb-0 mt-2"><small>Signed on: {{ $document->tenant_signed_at->format('F d, Y H:i') }}</small></p>
                                                            </div>
                                                        @else
                                                            <button class="btn btn-info btn-lg" id="signTenantBtn">
                                                                <i class="fas fa-pen"></i> Sign as Tenant
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <!-- Document Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Document Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Template:</strong><br>{{ $document->leaseTemplate->name ?? 'N/A' }}</p>
                            <p><strong>Tenant:</strong><br>{{ $document->tenant->name ?? 'N/A' }}</p>
                            <p><strong>Created:</strong><br>{{ $document->created_at->format('M d, Y') }}</p>
                            <p><strong>Status:</strong><br>
                                <span class="badge bg-{{ $document->status === 'signed' ? 'success' : ($document->status === 'pending_signatures' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('lease-documents.download-pdf', $document->id) }}" class="btn btn-primary w-100 mb-2" target="_blank">
                                <i class="fas fa-download"></i> Download PDF
                            </a>

                            @if($document->status !== 'signed')
                                <a href="{{ route('lease-documents.edit', $document->id) }}" class="btn btn-warning w-100 mb-2">
                                    <i class="fas fa-edit"></i> Edit Document
                                </a>
                            @endif

                            <button class="btn btn-secondary w-100" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Signature Status -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Signature Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <strong>Admin Signature:</strong>
                                <span class="badge bg-{{ $document->admin_signature ? 'success' : 'secondary' }}">
                                    {{ $document->admin_signature ? 'Signed' : 'Pending' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Tenant Signature:</strong>
                                <span class="badge bg-{{ $document->tenant_signature ? 'success' : 'secondary' }}">
                                    {{ $document->tenant_signature ? 'Signed' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="signatureModalLabel"><i class="fas fa-signature"></i> Add Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <p class="text-muted">Please draw your signature in the box below:</p>
                </div>
                <div class="signature-canvas-container">
                    <canvas id="signatureCanvas"></canvas>
                </div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-warning" id="clearSignatureBtn">
                        <i class="fas fa-eraser"></i> Clear Signature
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSignatureBtn">
                    <i class="fas fa-save"></i> Save Signature
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.documentId = {{ $document->id }};
</script>
<script src="{{ asset('js/signature_pad.js') }}"></script>
@endpush

@push('styles')
<style>
    .document-preview-container {
        background: #e0e0e0;
        padding: 30px;
    }

    .document-preview {
        background: white;
        padding: 60px 80px;
        max-width: 850px;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.15);
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        min-height: 800px;
    }

    .signature-area {
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 20px;
    }

    .signature-canvas-container {
        border: 2px solid #333;
        border-radius: 4px;
        background: white;
    }

    #signatureCanvas {
        width: 100%;
        height: 200px;
        cursor: crosshair;
    }

    .filled-placeholder {
        font-weight: 600;
        color: #000;
    }

    @media print {
        .btn, .card-header, .col-md-3 {
            display: none !important;
        }
        .col-md-9 {
            width: 100% !important;
        }
    }
</style>
@endpush
