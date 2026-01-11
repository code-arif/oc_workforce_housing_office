@extends('backend.app')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-eye text-primary"></i> Template Preview
                    </h1>
                    <p class="text-muted">{{ $template->name }} - Preview with sample data</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <a href="{{ route('lease-templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Templates
                    </a>
                    @can('lease.template.edit')
                    <a href="{{ route('lease-templates.edit', $template->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Template
                    </a>
                    @endcan
                </div>
            </div>
            <!-- PAGE HEADER END -->

            <div class="row">
                <!-- Template Info Sidebar -->
                <div class="col-xl-3 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Template Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small">Name</label>
                                <div class="fw-semibold">{{ $template->name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">File Type</label>
                                <div>
                                    <span class="badge badge-{{ $template->file_type == 'pdf' ? 'danger' : 'primary' }}">
                                        {{ strtoupper($template->file_type) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Status</label>
                                <div>
                                    <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Total Pages</label>
                                <div>{{ $template->total_pages ?? 1 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholders Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tags text-primary"></i> Placeholders ({{ count($placeholders) }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(count($placeholders) > 0)
                                <div class="placeholder-list">
                                    @foreach($placeholders as $index => $placeholder)
                                        <div class="placeholder-item d-flex justify-content-between align-items-center mb-2 p-2 bg-light">
                                            <div>
                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                <strong>{{ $placeholder['field'] ?? 'field' }}</strong>
                                            </div>
                                            <small class="text-muted">Page {{ $placeholder['page'] ?? 1 }}</small>
                                        </div>
                                        <div class="ps-4 mb-2 small text-muted">
                                            Sample: <span class="text-dark">{{ $dummyData[$placeholder['field']] ?? 'N/A' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No placeholders defined</p>
                            @endif
                        </div>
                    </div>

                    <!-- Signatures Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-signature text-warning"></i> Signatures ({{ count($signatures) }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(count($signatures) > 0)
                                <div class="signature-list">
                                    @foreach($signatures as $index => $signature)
                                        <div class="signature-item d-flex justify-content-between align-items-center mb-2 p-2 bg-light">
                                            <div>
                                                <span class="badge bg-warning text-dark me-2">{{ $index + 1 }}</span>
                                                <strong>{{ $signature['label'] ?? 'Signature' }}</strong>
                                            </div>
                                            <small class="text-muted">Page {{ $signature['page'] ?? 1 }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No signatures defined</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- PDF Preview with Overlays -->
                <div class="col-xl-9 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Document Preview</h5>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" id="zoomOut" title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button class="btn btn-outline-secondary" id="zoomIn" title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-outline-secondary" id="resetZoom" title="Reset Zoom">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body pdf-preview-body">
                            <div id="pdfContainer" class="pdf-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pdf-preview-body {
        background: #e9ecef;
        padding: 20px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .pdf-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .pdf-page-wrapper {
        position: relative;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        border-radius: 4px;
        overflow: visible;
    }

    .pdf-page-wrapper canvas {
        display: block;
    }

    .page-number {
        text-align: center;
        padding: 8px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        font-size: 12px;
        color: #6c757d;
    }

    /* Placeholder overlay */
    .placeholder-overlay {
        position: absolute;
        background: rgba(59, 130, 246, 0.12);
        border: 2px dashed rgba(59, 130, 246, 0.7);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        pointer-events: none;
    }

    .placeholder-overlay .overlay-content {
        padding: 2px 6px;
        font-size: 12px;
        font-weight: 600;
        color: #000000;
        background: rgba(255, 255, 255, 0);
        border-radius: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .placeholder-overlay.filled {
        background: rgba(34, 197, 94, 0);
        border-color: rgba(34, 197, 94, 0);
    }

    .placeholder-overlay.filled .overlay-content {
        color: #000000;
        font-weight: 500;
    }

    /* Signature overlay */
    .signature-overlay {
        position: absolute;
        background: rgba(245, 159, 11, 0);
        border: 2px dashed rgba(245, 159, 11, 0);
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .signature-overlay .sig-label {
        font-size: 12px;
        color: #92400e;
        margin-bottom: 4px;
    }

    .signature-overlay .sig-sample {
        font-family: 'Brush Script MT', cursive;
        font-size: 18px;
        color: #1e3a5f;
    }

    /* Card styling */
    .placeholder-list, .signature-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .badge-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .badge-primary {
        background: #dbeafe;
        color: #2563eb;
    }

    .badge-success {
        background: #d1fae5;
        color: #059669;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-warning {
        background: #fef3c7;
        color: #f59e0b;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configure PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    const placeholders = @json($placeholders);
    const signatures = @json($signatures);
    const dummyData = @json($dummyData);

    let currentScale = 1.2;
    const container = document.getElementById('pdfContainer');

    // Zoom controls
    document.getElementById('zoomIn').addEventListener('click', () => {
        currentScale = Math.min(currentScale + 0.2, 3);
        renderPdf();
    });

    document.getElementById('zoomOut').addEventListener('click', () => {
        currentScale = Math.max(currentScale - 0.2, 0.5);
        renderPdf();
    });

    document.getElementById('resetZoom').addEventListener('click', () => {
        currentScale = 1.2;
        renderPdf();
    });

    function renderPdf() {
        container.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading PDF...</p></div>';

        pdfjsLib.getDocument({ url: pdfUrl }).promise.then(function(pdf) {
            container.innerHTML = '';
            const totalPages = pdf.numPages;

            // Group placeholders and signatures by page
            const placeholdersByPage = groupByPage(placeholders);
            const signaturesByPage = groupByPage(signatures);

            // Render each page
            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                renderPage(pdf, pageNum, placeholdersByPage[pageNum] || [], signaturesByPage[pageNum] || []);
            }
        }).catch(function(error) {
            container.innerHTML = '<div class="alert alert-danger">Failed to load PDF: ' + error.message + '</div>';
            console.error('PDF load error:', error);
        });
    }

    function renderPage(pdf, pageNum, pagePlaceholders, pageSignatures) {
        pdf.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({ scale: currentScale });

            // Create page wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'pdf-page-wrapper';
            wrapper.style.width = viewport.width + 'px';

            // Create canvas
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            wrapper.appendChild(canvas);

            // Render PDF page
            page.render({
                canvasContext: context,
                viewport: viewport
            }).promise.then(function() {
                // Add placeholder overlays
                pagePlaceholders.forEach(function(p) {
                    const overlay = createPlaceholderOverlay(p, currentScale);
                    wrapper.appendChild(overlay);
                });

                // Add signature overlays
                pageSignatures.forEach(function(s) {
                    const overlay = createSignatureOverlay(s, currentScale);
                    wrapper.appendChild(overlay);
                });
            });

            // Page number
            const pageNumDiv = document.createElement('div');
            pageNumDiv.className = 'page-number';
            pageNumDiv.textContent = 'Page ' + pageNum + ' of ' + pdf.numPages;
            wrapper.appendChild(pageNumDiv);

            container.appendChild(wrapper);
        });
    }

    function createPlaceholderOverlay(p, scale) {
        const overlay = document.createElement('div');
        overlay.className = 'placeholder-overlay filled';

        const x = (parseFloat(p.x) || 0) * scale;
        const y = (parseFloat(p.y) || 0) * scale;
        const width = (parseFloat(p.width) || 150) * scale;
        const height = (parseFloat(p.height) || 24) * scale;

        overlay.style.left = x + 'px';
        overlay.style.top = y + 'px';
        overlay.style.width = width + 'px';
        overlay.style.height = height + 'px';

        const content = document.createElement('div');
        content.className = 'overlay-content';
        
        // Use dummy data value or field name
        const fieldName = p.field || 'field';
        const value = dummyData[fieldName] || '[' + fieldName + ']';
        content.textContent = value;

        overlay.appendChild(content);
        return overlay;
    }

    function createSignatureOverlay(s, scale) {
        const overlay = document.createElement('div');
        overlay.className = 'signature-overlay';

        const x = (parseFloat(s.x) || 0) * scale;
        const y = (parseFloat(s.y) || 0) * scale;
        const width = (parseFloat(s.width) || 200) * scale;
        const height = (parseFloat(s.height) || 60) * scale;

        overlay.style.left = x + 'px';
        overlay.style.top = y + 'px';
        overlay.style.width = width + 'px';
        overlay.style.height = height + 'px';

        const label = document.createElement('div');
        label.className = 'sig-label';
        label.textContent = s.label || 'Signature';
        overlay.appendChild(label);

        const sample = document.createElement('div');
        sample.className = 'sig-sample';
        sample.textContent = 'John Doe';
        overlay.appendChild(sample);

        return overlay;
    }

    function groupByPage(items) {
        const result = {};
        (items || []).forEach(function(item) {
            const page = parseInt(item.page) || 1;
            if (!result[page]) result[page] = [];
            result[page].push(item);
        });
        return result;
    }

    // Initial render
    renderPdf();
});
</script>
@endpush
