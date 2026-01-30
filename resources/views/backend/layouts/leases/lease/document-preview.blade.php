@extends('backend.app')

@section('title', 'Document Preview - ' . ($template->name ?? 'Lease Document'))

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-file-contract text-primary"></i> Document Preview
                    </h1>
                    <p class="text-muted">{{ $template->name ?? 'Lease Agreement' }} - Preview with actual lease data</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <a href="{{ route('leases.show', $lease->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Lease
                    </a>
                    @if($document)
                        <a href="{{ route('lease-documents.download-pdf', $document->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    @endif
                </div>
            </div>
            <!-- PAGE HEADER END -->

            <div class="row">
                <!-- Template Info Sidebar -->
                <div class="col-xl-3 col-lg-4">
                    <!-- Lease Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-home text-primary"></i> Lease Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small">Property</label>
                                <div class="fw-semibold">{{ $lease->property->name ?? 'N/A' }}</div>
                            </div>
                            @php
                                $assignment = $lease->assignments->where('is_current', true)->first();
                                $bed = $assignment?->bed;
                                $room = $bed?->room;
                                $unit = $room?->unit;
                            @endphp
                            <div class="mb-3">
                                <label class="text-muted small">Unit / Room / Bed</label>
                                <div class="fw-semibold">
                                    {{ $unit?->unit_number ?? '' }}
                                    {{ $room ? '/ Room ' . $room->room_number : '' }}
                                    {{ $bed ? '/ ' . $bed->bed_label : '' }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Lease Term</label>
                                <div class="fw-semibold">
                                    {{ date('M d, Y', strtotime($lease->start_date)) }} - {{ date('M d, Y', strtotime($lease->end_date)) }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Monthly Rent</label>
                                <div class="fw-bold text-primary fs-5">${{ number_format($lease->rent_amount, 2) }}</div>
                            </div>
                            @if($lease->deposit_amount > 0)
                            <div class="mb-3">
                                <label class="text-muted small">Security Deposit</label>
                                <div class="fw-semibold">${{ number_format($lease->deposit_amount, 2) }}</div>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label class="text-muted small">Template</label>
                                <div>
                                    <span class="badge badge-{{ $template->file_type == 'pdf' ? 'danger' : 'primary' }}">
                                        {{ strtoupper($template->file_type ?? 'PDF') }}
                                    </span>
                                    <span class="ms-1">{{ $template->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tenant Info -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user text-info"></i> Tenant
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                $profile = $lease->tenant?->profile;
                                $tenantName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'N/A';
                                $avatar = $profile && $profile->avatar ? asset($profile->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($tenantName) . '&background=random';
                            @endphp
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $avatar }}" alt="Tenant" class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
                                <div>
                                    <strong>{{ $tenantName }}</strong>
                                    <small class="text-muted d-block">{{ $lease->tenant?->email ?? 'N/A' }}</small>
                                </div>
                            </div>
                            @if($profile?->phone)
                            <div class="small text-muted">
                                <i class="fas fa-phone me-1"></i> {{ $profile->phone }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Placeholders Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tags text-primary"></i> Data Fields ({{ count($placeholders) }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(count($placeholders) > 0)
                                <div class="placeholder-list">
                                    @foreach($placeholders as $index => $placeholder)
                                        @php
                                            $fieldName = $placeholder['field'] ?? 'field';
                                            $fieldValue = $leaseData[$fieldName] ?? 'N/A';
                                        @endphp
                                        <div class="placeholder-item d-flex justify-content-between align-items-center mb-2 p-2 bg-light  ">
                                            <div>
                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                <strong class="small">{{ ucwords(str_replace('_', ' ', $fieldName)) }}</strong>
                                            </div>
                                            <small class="text-muted">Pg {{ $placeholder['page'] ?? 1 }}</small>
                                        </div>
                                        <div class="ps-4 mb-2 small">
                                            <span class="text-success fw-semibold">{{ $fieldValue }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0 small">No placeholders defined in template</p>
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
                                        <div class="signature-item d-flex justify-content-between align-items-center mb-2 p-2 bg-light ">
                                            <div>
                                                <span class="badge bg-warning text-dark me-2">{{ $index + 1 }}</span>
                                                <strong class="small">{{ $signature['label'] ?? 'Signature' }}</strong>
                                            </div>
                                            <small class="text-muted">Pg {{ $signature['page'] ?? 1 }}</small>
                                        </div>
                                        <div class="ps-4 mb-2 small">
                                            @if($signature['label'] == 'Tenant Signature' || str_contains(strtolower($signature['label'] ?? ''), 'tenant'))
                                                @if($document->tenant_signed_at)
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Signed {{ $document->tenant_signed_at->format('M d, Y') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            @else
                                                @if($document->admin_signed_at)
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Signed {{ $document->admin_signed_at->format('M d, Y') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0 small">No signatures defined</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog text-secondary"></i> Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($document)
                                <a href="{{ route('lease-documents.download-pdf', $document->id) }}" class="btn btn-primary w-100 mb-2" target="_blank">
                                    <i class="fas fa-download me-1"></i> Download PDF
                                </a>
                                @if(!$document->admin_signed_at || !$document->tenant_signed_at)
                                    <a href="{{ route('lease-documents.show', $document->id) }}" class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-edit me-1"></i> Sign Document
                                    </a>
                                @endif
                            @endif
                            <button class="btn btn-outline-secondary w-100" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PDF Preview with Overlays -->
                <div class="col-xl-9 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-pdf text-danger me-2"></i>Document Preview
                            </h5>
                            <div class="d-flex align-items-center gap-3">
                                @if($document)
                                    @if($document->tenant_signed_at && $document->admin_signed_at)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Fully Signed</span>
                                    @elseif($document->tenant_signed_at)
                                        <span class="badge bg-info"><i class="fas fa-clock me-1"></i> Pending Admin Signature</span>
                                    @elseif($document->admin_signed_at)
                                        <span class="badge bg-warning"><i class="fas fa-clock me-1"></i> Pending Tenant Signature</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-edit me-1"></i> Pending Signatures</span>
                                    @endif
                                @endif
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
                        </div>
                        <div class="card-body pdf-preview-body">
                            @if($pdfUrl)
                                <div id="pdfContainer" class="pdf-container"></div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                                    <h5>No PDF Available</h5>
                                    <p class="text-muted">The template does not have a PDF file associated with it.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Pad Modal -->
<div id="signatureModal" class="signature-modal">
    <div class="signature-modal-content">
        <div class="signature-modal-header">
            <h5><i class="fas fa-signature me-2"></i><span id="signatureModalTitle">Add Signature</span></h5>
            <button type="button" class="signature-modal-close" onclick="closeSignatureModal()">&times;</button>
        </div>
        <div class="signature-canvas-wrapper" id="signatureCanvasWrapper">
            <canvas id="signatureCanvas" class="signature-canvas"></canvas>
            <div class="signature-canvas-hint">
                <i class="fas fa-pen me-2"></i>Draw your signature here
            </div>
        </div>
        <div class="text-muted small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Use your mouse or finger to draw your signature above
        </div>
        <div class="signature-modal-actions">
            <button type="button" class="btn btn-outline-secondary" onclick="clearSignature()">
                <i class="fas fa-eraser me-1"></i> Clear
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="closeSignatureModal()">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button type="button" class="btn btn-success" onclick="saveSignature()" id="saveSignatureBtn">
                <i class="fas fa-check me-1"></i> Save Signature
            </button>
        </div>
    </div>
</div>

<!-- Text Input Modal -->
<div id="textInputModal" class="signature-modal">
    <div class="signature-modal-content" style="max-width: 600px;">
        <div class="signature-modal-header">
            <h5><i class="fas fa-edit me-2"></i><span id="textInputModalTitle">Enter Text</span></h5>
            <button type="button" class="signature-modal-close" onclick="closeTextInputModal()">&times;</button>
        </div>
        <div class="mb-3">
            <label for="customTextArea" class="form-label fw-semibold">Text Content</label>
            <textarea 
                id="customTextArea" 
                class="form-control" 
                rows="6" 
                placeholder="Enter your text here..."
                style="font-size: 14px; resize: vertical;"
            ></textarea>
            <div class="text-muted small mt-2">
                <i class="fas fa-info-circle me-1"></i>
                This text will appear in the document at the designated location.
            </div>
        </div>
        <div class="signature-modal-actions">
            <button type="button" class="btn btn-outline-secondary" onclick="clearTextInput()">
                <i class="fas fa-eraser me-1"></i> Clear
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="closeTextInputModal()">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="saveTextInput()" id="saveTextBtn">
                <i class="fas fa-check me-1"></i> Save Text
            </button>
        </div>
    </div>
</div>
@endsection



@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configure PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = @json($pdfUrl);
        const allPlaceholders = @json($placeholders);
        const signatures = @json($signatures);
        const leaseData = @json($leaseData);
        const documentId = @json($document->id);
        const documentData = {
            tenantSigned: @json($document->tenant_signed_at ? true : false),
            adminSigned: @json($document->admin_signed_at ? true : false),
            tenantSignature: @json($document->tenant_signature ?? null),
            adminSignature: @json($document->admin_signature ?? null)
        };
        
        // Separate text input fields from regular placeholders
        const placeholders = allPlaceholders.filter(p => p.type !== 'text_input');
        const textInputFields = allPlaceholders.filter(p => p.type === 'text_input');
        
        // Store custom text values (from database or user input)
        let customTextValuesRaw = @json($document->custom_fields ?? []);
        // Ensure it's an object, not an array
        let customTextValues = (customTextValuesRaw && typeof customTextValuesRaw === 'object' && !Array.isArray(customTextValuesRaw)) 
            ? customTextValuesRaw 
            : {};

        if (!pdfUrl) {
            console.warn('No PDF URL provided');
            return;
        }

        let currentScale = 1.0;
        let pdfDoc = null;
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

            const loadingTask = pdfjsLib.getDocument({ url: pdfUrl });
            
            loadingTask.promise.then(function(pdf) {
                pdfDoc = pdf;
                container.innerHTML = '';
                const totalPages = pdf.numPages;

                // Group placeholders, text inputs, and signatures by page
                const placeholdersByPage = groupByPage(placeholders);
                const textInputsByPage = groupByPage(textInputFields);
                const signaturesByPage = groupByPage(signatures);

                // Render pages sequentially to maintain order
                let renderPromise = Promise.resolve();
                for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                    renderPromise = renderPromise.then(() => {
                        return renderPage(pdf, pageNum, totalPages, placeholdersByPage[pageNum] || [], signaturesByPage[pageNum] || [], textInputsByPage[pageNum] || []);
                    });
                }
            }).catch(function(error) {
                container.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load PDF: ' + error.message + '</div>';
                console.error('PDF load error:', error);
            });
        }

        function renderPage(pdf, pageNum, totalPages, pagePlaceholders, pageSignatures, pageTextInputs) {
            return pdf.getPage(pageNum).then(function(page) {
                const viewport = page.getViewport({ scale: currentScale });

                // Create page wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'pdf-page-wrapper';
                wrapper.style.width = viewport.width + 'px';
                wrapper.dataset.pageNum = pageNum;

                // Create canvas
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                wrapper.appendChild(canvas);

                // Render PDF page
                return page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise.then(function() {
                    // Add placeholder overlays with actual lease data
                    pagePlaceholders.forEach(function(p) {
                        const overlay = createPlaceholderOverlay(p, currentScale);
                        wrapper.appendChild(overlay);
                    });

                    // Add text input overlays
                    pageTextInputs.forEach(function(t) {
                        const overlay = createTextInputOverlay(t, currentScale);
                        wrapper.appendChild(overlay);
                    });

                    // Add signature overlays
                    pageSignatures.forEach(function(s) {
                        const overlay = createSignatureOverlay(s, currentScale);
                        wrapper.appendChild(overlay);
                    });

                    // Page number
                    const pageNumDiv = document.createElement('div');
                    pageNumDiv.className = 'page-number';
                    pageNumDiv.textContent = 'Page ' + pageNum + ' of ' + totalPages;
                    wrapper.appendChild(pageNumDiv);

                    container.appendChild(wrapper);
                });
            });
        }

        function createPlaceholderOverlay(p, scale) {
            const overlay = document.createElement('div');
            overlay.className = 'placeholder-overlay';

            // Coordinates are stored at scale 1.0, multiply by current scale
            const x = (parseFloat(p.x) || 0) * scale;
            const y = (parseFloat(p.y) || 0) * scale;
            const width = (parseFloat(p.width) || 150) * 1;
            const height = (parseFloat(p.height) || 20) * 1;

            overlay.style.left = x + 'px';
            overlay.style.top = y + 'px';
            overlay.style.width = width + 'px';
            overlay.style.height = height + 'px';

            const content = document.createElement('div');
            content.className = 'overlay-content';
            
            // Adjust font size based on scale
            const fontSize = Math.max(8, Math.min(14, 11 * scale));
            content.style.fontSize = fontSize + 'px';
            
            // Use actual lease data value
            const fieldName = p.field || 'field';
            const value = leaseData[fieldName] || '';
            content.textContent = value;
            content.title = fieldName + ': ' + value;

            overlay.appendChild(content);
            return overlay;
        }

        function createTextInputOverlay(t, scale) {
            const overlay = document.createElement('div');
            overlay.className = 'text-input-overlay';

            // Coordinates are stored at scale 1.0, multiply by current scale
            const x = (parseFloat(t.x) || 0) * scale;
            const y = (parseFloat(t.y) || 0) * scale;
            const width = (parseFloat(t.width) || 200) * scale;
            const height = (parseFloat(t.height) || 35) * scale;

            overlay.style.left = x + 'px';
            overlay.style.top = y + 'px';
            overlay.style.width = width + 'px';
            overlay.style.height = height + 'px';

            const fieldId = t.id || t.field;
            const label = t.customLabel || t.label || 'Custom Text';
            const savedValue = customTextValues[fieldId] || '';

            overlay.dataset.fieldId = fieldId;
            overlay.dataset.label = label;

            if (savedValue) {
                overlay.classList.add('filled');
            }

            const content = document.createElement('div');
            content.className = 'text-content';
            
            if (savedValue) {
                content.textContent = savedValue;
            } else {
                const placeholder = document.createElement('span');
                placeholder.className = 'placeholder-text';
                placeholder.textContent = 'Click to add text: ' + label;
                content.appendChild(placeholder);
            }
            
            overlay.appendChild(content);

            const icon = document.createElement('i');
            icon.className = 'fas fa-edit edit-icon';
            overlay.appendChild(icon);

            // Add click handler
            overlay.addEventListener('click', function() {
                openTextInputModal(fieldId, label, savedValue);
            });

            return overlay;
        }

        function createSignatureOverlay(s, scale) {
            const overlay = document.createElement('div');
            overlay.className = 'signature-overlay';

            // Coordinates are stored at scale 1.0, multiply by current scale
            const x = (parseFloat(s.x) || 0) * scale;
            const y = (parseFloat(s.y) || 0) * scale;
            const width = (parseFloat(s.width) || 200) * scale;
            const height = (parseFloat(s.height) || 60) * scale;

            overlay.style.left = x + 'px';
            overlay.style.top = y + 'px';
            overlay.style.width = width + 'px';
            overlay.style.height = height + 'px';

            const label = s.label || 'Signature';
            const isTenantSig = label.toLowerCase().includes('tenant');
            const isSigned = isTenantSig ? documentData.tenantSigned : documentData.adminSigned;
            const signatureData = isTenantSig ? documentData.tenantSignature : documentData.adminSignature;

            if (isSigned && signatureData) {
                overlay.classList.add('signed');
                
                // Show actual signature image
                const sigImg = document.createElement('img');
                sigImg.src = signatureData;
                sigImg.className = 'signature-image';
                sigImg.alt = label;
                overlay.appendChild(sigImg);
            } else if (isSigned) {
                overlay.classList.add('signed');
                
                // Show signed indicator without image
                const statusBadge = document.createElement('div');
                statusBadge.className = 'sig-status bg-success text-white';
                statusBadge.innerHTML = '<i class="fas fa-check me-1"></i>Signed';
                overlay.appendChild(statusBadge);
            } else {
                // Pending signature - make clickable
                overlay.classList.add('pending');
                overlay.dataset.signatureType = isTenantSig ? 'tenant' : 'admin';
                overlay.dataset.signatureLabel = label;
                
                const sigLabel = document.createElement('div');
                sigLabel.className = 'sig-label';
                sigLabel.textContent = label;
                overlay.appendChild(sigLabel);

                const clickIcon = document.createElement('div');
                clickIcon.innerHTML = '<i class="fas fa-pen-fancy" style="font-size: ' + (16 * scale) + 'px; color: #f59e0b;"></i>';
                overlay.appendChild(clickIcon);

                const clickHint = document.createElement('div');
                clickHint.className = 'sig-click-hint';
                clickHint.textContent = 'Click to sign';
                overlay.appendChild(clickHint);

                // Add click handler to open signature pad
                overlay.addEventListener('click', function() {
                    openSignatureModal(isTenantSig ? 'tenant' : 'admin', label);
                });
            }

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

        // ===== Signature Pad Functionality =====
        let signatureCanvas, signatureCtx;
        let isDrawing = false;
        let lastX = 0, lastY = 0;
        let currentSignatureType = null;
        let hasDrawn = false;

        function initSignatureCanvas() {
            signatureCanvas = document.getElementById('signatureCanvas');
            signatureCtx = signatureCanvas.getContext('2d');
            
            // Set canvas size
            const wrapper = document.getElementById('signatureCanvasWrapper');
            signatureCanvas.width = wrapper.offsetWidth - 4;
            signatureCanvas.height = 200;
            
            // Set drawing style
            signatureCtx.strokeStyle = '#1e3a5f';
            signatureCtx.lineWidth = 2;
            signatureCtx.lineCap = 'round';
            signatureCtx.lineJoin = 'round';

            // Mouse events
            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseout', stopDrawing);

            // Touch events
            signatureCanvas.addEventListener('touchstart', handleTouchStart);
            signatureCanvas.addEventListener('touchmove', handleTouchMove);
            signatureCanvas.addEventListener('touchend', stopDrawing);
        }

        function startDrawing(e) {
            isDrawing = true;
            hasDrawn = true;
            document.getElementById('signatureCanvasWrapper').classList.add('has-signature');
            [lastX, lastY] = getCoords(e);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            
            const [x, y] = getCoords(e);
            
            signatureCtx.beginPath();
            signatureCtx.moveTo(lastX, lastY);
            signatureCtx.lineTo(x, y);
            signatureCtx.stroke();
            
            [lastX, lastY] = [x, y];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function handleTouchStart(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            signatureCanvas.dispatchEvent(mouseEvent);
        }

        function handleTouchMove(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            signatureCanvas.dispatchEvent(mouseEvent);
        }

        function getCoords(e) {
            const rect = signatureCanvas.getBoundingClientRect();
            const x = (e.clientX || e.touches?.[0]?.clientX || 0) - rect.left;
            const y = (e.clientY || e.touches?.[0]?.clientY || 0) - rect.top;
            return [x, y];
        }

        window.openSignatureModal = function(type, label) {
            currentSignatureType = type;
            document.getElementById('signatureModalTitle').textContent = label || 'Add Signature';
            document.getElementById('signatureModal').classList.add('show');
            
            // Initialize canvas after modal is visible
            setTimeout(() => {
                initSignatureCanvas();
                clearSignature();
            }, 100);
        };

        window.closeSignatureModal = function() {
            document.getElementById('signatureModal').classList.remove('show');
            currentSignatureType = null;
            hasDrawn = false;
        };

        window.clearSignature = function() {
            if (signatureCtx) {
                signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            }
            hasDrawn = false;
            document.getElementById('signatureCanvasWrapper').classList.remove('has-signature');
        };

        window.saveSignature = function() {
            if (!hasDrawn) {
                alert('Please draw your signature first');
                return;
            }

            const signatureData = signatureCanvas.toDataURL('image/png');
            const saveBtn = document.getElementById('saveSignatureBtn');
            const originalText = saveBtn.innerHTML;
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            // Determine which endpoint to call
            const endpoint = currentSignatureType === 'tenant' 
                ? '{{ route("lease-documents.sign-tenant", ":id") }}'.replace(':id', documentId)
                : '{{ route("lease-documents.sign-admin", ":id") }}'.replace(':id', documentId);

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    signature: signatureData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update local state
                    if (currentSignatureType === 'tenant') {
                        documentData.tenantSigned = true;
                        documentData.tenantSignature = signatureData;
                    } else {
                        documentData.adminSigned = true;
                        documentData.adminSignature = signatureData;
                    }
                    
                    closeSignatureModal();
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Signature saved successfully!');
                    } else {
                        alert('Signature saved successfully!');
                    }
                    
                    // Re-render PDF to show updated signature
                    renderPdf();
                    
                    // Update sidebar signature status
                    updateSidebarSignatureStatus();
                } else {
                    alert(data.message || 'Failed to save signature');
                }
            })
            .catch(error => {
                console.error('Error saving signature:', error);
                alert('Failed to save signature. Please try again.');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            });
        };

        function updateSidebarSignatureStatus() {
            // Reload the page to update sidebar (simple approach)
            // Alternatively, you could update the DOM directly
            location.reload();
        }

        // ===== Text Input Modal Functionality =====
        let currentTextFieldId = null;

        window.openTextInputModal = function(fieldId, label, currentValue) {
            currentTextFieldId = fieldId;
            document.getElementById('textInputModalTitle').textContent = label || 'Enter Text';
            document.getElementById('customTextArea').value = currentValue || '';
            document.getElementById('textInputModal').classList.add('show');
            
            // Focus on textarea
            setTimeout(() => {
                document.getElementById('customTextArea').focus();
            }, 100);
        };

        window.closeTextInputModal = function() {
            document.getElementById('textInputModal').classList.remove('show');
            currentTextFieldId = null;
        };

        window.clearTextInput = function() {
            document.getElementById('customTextArea').value = '';
        };

        window.saveTextInput = function() {
            const textValue = document.getElementById('customTextArea').value.trim();
           
            
            if (!textValue) {
                alert('Please enter some text');
                return;
            }

            const saveBtn = document.getElementById('saveTextBtn');
            const originalText = saveBtn.innerHTML;
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            // Update custom text values
            customTextValues[currentTextFieldId] = textValue;
             console.log(textValue);
            console.log(currentTextFieldId);
            console.log(customTextValues);
            
            // Save to database
            fetch('{{ route("lease-documents.update-custom-fields", ":id") }}'.replace(':id', documentId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    custom_fields: customTextValues
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeTextInputModal();
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Text saved successfully!');
                    } else {
                        alert('Text saved successfully!');
                    }
                    
                    // Re-render PDF to show updated text
                    renderPdf();
                } else {
                    alert(data.message || 'Failed to save text');
                    customTextValues[currentTextFieldId] = ''; // Revert
                }
            })
            .catch(error => {
                console.error('Error saving text:', error);
                alert('Failed to save text. Please try again.');
                customTextValues[currentTextFieldId] = ''; // Revert
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            });
        };

        // Initial render
        renderPdf();
    });
</script>
@endpush
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

    /* Placeholder overlay - filled with actual data */
    .placeholder-overlay {
        position: absolute;
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        overflow: hidden;
        pointer-events: none;
        box-sizing: border-box;
    }

    .placeholder-overlay .overlay-content {
        padding: 10px 20px;
        font-size: 10px;
        font-weight: 500;
        color: #000000;
        background: transparent;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        line-height: 1.2;
    }

    /* Text Input Overlay */
    .text-input-overlay {
        position: absolute;
        background: rgba(59, 130, 246, 0.08);
        border: 2px dashed rgba(59, 130, 246, 0.5);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 8px 12px;
        box-sizing: border-box;
        pointer-events: auto;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .text-input-overlay:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.8);
        transform: scale(1.01);
    }

    .text-input-overlay.filled {
        background: rgba(34, 197, 94, 0.08);
        border-color: rgba(34, 197, 94, 0.5);
        border-style: solid;
    }

    .text-input-overlay.filled:hover {
        background: rgba(34, 197, 94, 0.12);
    }

    .text-input-overlay .text-content {
        font-size: 11px;
        color: #1e40af;
        flex: 1;
        word-wrap: break-word;
        line-height: 1.4;
    }

    .text-input-overlay.filled .text-content {
        color: #000000;
        font-weight: 500;
    }

    .text-input-overlay .edit-icon {
        margin-left: 8px;
        color: #3b82f6;
        font-size: 12px;
    }

    .text-input-overlay .placeholder-text {
        color: #94a3b8;
        font-style: italic;
    }

    /* Signature overlay */
    .signature-overlay {
        position: absolute;
        background: rgba(245, 159, 11, 0.05);
        border: 2px dashed rgba(245, 159, 11, 0.5);
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        box-sizing: border-box;
    }

    .signature-overlay.signed {
        background: rgba(34, 197, 94, 0.05);
        border-color: rgba(34, 197, 94, 0.5);
        border-style: solid;
    }

    .signature-overlay.pending {
        pointer-events: auto;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .signature-overlay.pending:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.7);
        transform: scale(1.02);
    }

    .signature-overlay .sig-label {
        font-size: 10px;
        color: #92400e;
        margin-bottom: 4px;
        font-weight: 500;
    }

    .signature-overlay .sig-click-hint {
        font-size: 9px;
        color: #6b7280;
        margin-top: 2px;
    }

    .signature-overlay .sig-status {
        font-size: 10px;
        padding: 3px 10px;
        border-radius: 10px;
    }

    .signature-overlay .signature-image {
        max-width: 90%;
        max-height: 80%;
        object-fit: contain;
    }

    /* Signature Pad Modal */
    .signature-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .signature-modal.show {
        display: flex;
    }

    .signature-modal-content {
        background: white;
        border-radius: 12px;
        padding: 24px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .signature-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .signature-modal-header h5 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
    }

    .signature-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        line-height: 1;
    }

    .signature-modal-close:hover {
        color: #dc3545;
    }

    .signature-canvas-wrapper {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: #fafafa;
        margin-bottom: 15px;
        position: relative;
    }

    .signature-canvas {
        width: 100%;
        height: 200px;
        cursor: crosshair;
        display: block;
        border-radius: 6px;
    }

    .signature-canvas-hint {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #adb5bd;
        font-size: 14px;
        pointer-events: none;
        transition: opacity 0.3s;
    }

    .signature-canvas-wrapper.has-signature .signature-canvas-hint {
        opacity: 0;
    }

    .signature-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .signature-modal-actions .btn {
        padding: 10px 20px;
    }

    /* Card styling */
    .placeholder-list, .signature-list {
        max-height: 250px;
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

    /* Print Styles */
    @media print {
        .col-xl-3, .col-lg-4,
        .page-header,
        .btn,
        .signature-modal {
            display: none !important;
        }

        .col-xl-9, .col-lg-8 {
            width: 100% !important;
            max-width: 100% !important;
        }

        .pdf-preview-body {
            padding: 0;
            background: white;
            max-height: none;
        }
    }
</style>
@endpush