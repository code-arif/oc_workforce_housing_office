@extends('backend.app')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Upload New Lease Template</h1>
                    <p class="text-muted">Upload a pre-formatted lease document and add dynamic placeholders</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Templates
                    </a>
                </div>
            </div>
            <!-- PAGE HEADER END -->

            <div class="row">
                <!-- Left Side - Instructions -->
                <div class="col-lg-4">
                    <!-- Quick Guide -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book-open"></i> Quick Guide
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline-steps">
                                <div class="timeline-step">
                                    <div class="timeline-content">
                                        <div class="step-number">1</div>
                                        <h6>Upload Document</h6>
                                        <p class="text-muted small">Upload your lease agreement in PDF format</p>
                                    </div>
                                </div>
                                <div class="timeline-step">
                                    <div class="timeline-content">
                                        <div class="step-number">2</div>
                                        <h6>System Conversion</h6>
                                        <p class="text-muted small">System will read and convert your document to editable format</p>
                                    </div>
                                </div>
                                <div class="timeline-step">
                                    <div class="timeline-content">
                                        <div class="step-number">3</div>
                                        <h6>Add Placeholders</h6>
                                        <p class="text-muted small">Drag and drop dynamic placeholders into the document</p>
                                    </div>
                                </div>
                                <div class="timeline-step">
                                    <div class="timeline-content">
                                        <div class="step-number">4</div>
                                        <h6>Save & Use</h6>
                                        <p class="text-muted small">Save template and use it to generate leases</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Card -->
                    <div class="card border-warning">
                        <div class="card-header bg-warning-transparent">
                            <h5 class="card-title mb-0 text-warning">
                                <i class="fas fa-lightbulb"></i> Best Practices
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="tips-list mb-0">
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span><strong>Use PDF format</strong> for better formatting preservation</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span><strong>Remove signatures</strong> from the template before uploading</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span><strong>Use standard fonts</strong> like Arial or Times New Roman</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span><strong>Clear formatting</strong> with proper headings and spacing</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span><strong>Keep file size</strong> under 10MB for optimal performance</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Supported Placeholders -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tags"></i> Available Placeholders
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="placeholder-preview">
                                <div class="placeholder-category mb-3">
                                    <h6 class="text-primary"><i class="fas fa-user"></i> Tenant Info</h6>
                                    <div class="placeholder-tags">
                                        <span class="badge bg-primary-transparent">Tenant Name</span>
                                        <span class="badge bg-primary-transparent">Tenant Email</span>
                                        <span class="badge bg-primary-transparent">Tenant Phone</span>
                                    </div>
                                </div>
                                <div class="placeholder-category mb-3">
                                    <h6 class="text-success"><i class="fas fa-building"></i> Property</h6>
                                    <div class="placeholder-tags">
                                        <span class="badge bg-success-transparent">Property Address</span>
                                        <span class="badge bg-success-transparent">Property Type</span>
                                    </div>
                                </div>
                                <div class="placeholder-category mb-3">
                                    <h6 class="text-warning"><i class="fas fa-file-contract"></i> Lease Terms</h6>
                                    <div class="placeholder-tags">
                                        <span class="badge bg-warning-transparent">Start Date</span>
                                        <span class="badge bg-warning-transparent">End Date</span>
                                        <span class="badge bg-warning-transparent">Monthly Rent</span>
                                        <span class="badge bg-warning-transparent">Security Deposit</span>
                                    </div>
                                </div>
                                <div class="placeholder-category">
                                    <h6 class="text-info"><i class="fas fa-signature"></i> Signatures</h6>
                                    <div class="placeholder-tags">
                                        <span class="badge bg-info-transparent">Admin Signature</span>
                                        <span class="badge bg-info-transparent">Tenant Signature</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Upload Form -->
                <div class="col-lg-8">
                    <form action="{{ route('lease-templates.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data" 
                          id="uploadForm">
                        @csrf

                        <!-- Template Info Card -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i> Template Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- Template Name -->
                                        <div class="form-group mb-4">
                                            <label for="name" class="form-label required">
                                                Template Name
                                            </label>
                                            <input type="text" 
                                                   name="name" 
                                                   id="name" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   placeholder="e.g., Standard Residential Lease Agreement 2024"
                                                   value="{{ old('name') }}"
                                                   required>
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Give your template a clear, descriptive name
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <!-- Template Description -->
                                        <div class="form-group mb-4">
                                            <label for="description" class="form-label">
                                                Description <span class="text-muted">(Optional)</span>
                                            </label>
                                            <textarea name="description" 
                                                      id="description" 
                                                      rows="3" 
                                                      class="form-control @error('description') is-invalid @enderror"
                                                      placeholder="Brief description of when to use this template...">{{ old('description') }}</textarea>
                                            @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload Card -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload Document
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="upload-zone" id="uploadZone">
                                    <div class="upload-zone-content">
                                        <div class="upload-icon-wrapper">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <h4 class="upload-title">Drop your file here</h4>
                                        <p class="upload-subtitle">or click to browse</p>
                                        <div class="upload-formats">
                                            <span class="format-badge">
                                                <i class="fas fa-file-pdf"></i>Only PDF
                                            </span>
                                            
                                        </div>
                                        <input type="file" 
                                               name="template_file" 
                                               id="template_file" 
                                               class="d-none @error('template_file') is-invalid @enderror" 
                                               accept=".pdf,.docx"
                                               required>
                                        <button type="button" class="btn btn-primary btn-lg mt-3" id="browseBtn">
                                            <i class="fas fa-folder-open"></i> Browse Files
                                        </button>
                                    </div>

                                    <!-- File Preview (Hidden by default) -->
                                    <div class="file-preview" id="filePreview" style="display: none;">
                                        <div class="file-preview-content">
                                            <div class="file-icon" id="fileIcon">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div class="file-details">
                                                <h5 id="fileName" class="mb-1"></h5>
                                                <div class="file-meta">
                                                    <span class="badge badge-primary" id="fileType"></span>
                                                    <span class="text-muted" id="fileSize"></span>
                                                </div>
                                                <div class="progress mt-2" id="uploadProgress" style="display: none;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                         role="progressbar" 
                                                         style="width: 0%"
                                                         id="progressBar"></div>
                                                </div>
                                            </div>
                                            <div class="file-actions">
                                                <button type="button" class="btn btn-danger btn-sm" id="removeFileBtn">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @error('template_file')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                                @enderror

                                <div class="upload-info mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Accepted formats:</strong> PDF (.pdf) | 
                                        <strong>Maximum size:</strong> 10MB
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-upload"></i> Upload & Continue to Editor
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Timeline Steps */
    .timeline-steps {
        position: relative;
    }

    .timeline-step {
        position: relative;
        padding-left: 50px;
        padding-bottom: 25px;
    }

    .timeline-step:not(:last-child):before {
        content: '';
        position: absolute;
        left: 17px;
        top: 35px;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .step-number {
        position: absolute;
        left: 0;
        top: 0;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
        color: #2c3e50;
    }

    /* Tips List */
    .tips-list {
        list-style: none;
        padding: 0;
    }

    .tips-list li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 12px;
        padding-left: 10px;
    }

    .tips-list li i {
        margin-right: 10px;
        margin-top: 3px;
        font-size: 14px;
    }

    .tips-list li span {
        flex: 1;
        font-size: 13px;
        line-height: 1.5;
    }

    /* Placeholder Preview */
    .placeholder-category h6 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .placeholder-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .placeholder-tags .badge {
        font-size: 11px;
        padding: 15px 12px;
        font-weight: 500;
    }

    /* Upload Zone */
    .upload-zone {
        border: 3px dashed #d1d7e0;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8f9fa;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .upload-zone.drag-over {
        border-color: #667eea;
        background: #f0f3ff;
        transform: scale(1.02);
    }

    .upload-zone-content {
        width: 100%;
    }

    .upload-icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .upload-icon-wrapper i {
        font-size: 36px;
        color: white;
    }

    .upload-title {
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .upload-subtitle {
        color: #6c757d;
        margin-bottom: 20px;
    }

    .upload-formats {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-bottom: 15px;
    }

    .format-badge {
        background: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .format-badge i {
        margin-right: 5px;
    }

    /* File Preview */
    .file-preview {
        width: 100%;
    }

    .file-preview-content {
        display: flex;
        align-items: center;
        gap: 20px;
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .file-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .file-icon i {
        font-size: 28px;
        color: white;
    }

    .file-details {
        flex: 1;
    }

    .file-details h5 {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .file-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 5px;
    }

    .file-actions {
        flex-shrink: 0;
    }

    /* Cards */
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .card-header {
        background: white;
        border-bottom: 2px solid #f1f3f5;
        padding: 15px 20px;
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
    }

    .card-primary .card-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
        color: white;
    }

    .card-primary .card-title {
        color: white;
    }

    .card-info .card-header {
        background: linear-gradient(135deg, rgba(17, 153, 142, 0.3) 0%, rgba(56, 239, 126, 0.3) 100%);
        color: white;
    }

    .card-info .card-title {
        color: white;
    }

    /* Form Elements */
    .form-label.required:after {
        content: ' *';
        color: #dc3545;
    }

    .form-control-lg {
        padding: 12px 16px;
        font-size: 15px;
    }

    /* Buttons */
    .btn-lg {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 500;
    }

    /* Background transparent classes */
    .bg-primary-transparent {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .bg-success-transparent {
        background: rgba(56, 239, 125, 0.1);
        color: #11998e;
    }

    .bg-warning-transparent {
        background: rgba(255, 193, 7, 0.1);
        color: #f39c12;
    }

    .bg-info-transparent {
        background: rgba(0, 188, 212, 0.1);
        color: #00bcd4;
    }

    .upload-info {
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const uploadZone = $('#uploadZone');
    const uploadZoneContent = $('.upload-zone-content');
    const filePreview = $('#filePreview');
    const fileInput = $('#template_file');
    const browseBtn = $('#browseBtn');
    const removeFileBtn = $('#removeFileBtn');
    const submitBtn = $('#submitBtn');
    const uploadForm = $('#uploadForm');

    // Browse button click
    browseBtn.on('click', function(e) {
        e.stopPropagation();
        fileInput.click();
    });

    // Upload zone click (excluding button and input)
    uploadZone.on('click', function(e) {
        // Don't trigger if clicking on button, input, or file preview
        if ($(e.target).closest('.btn, input, .file-preview').length > 0) {
            return;
        }
        
        if (!filePreview.is(':visible')) {
            fileInput.click();
        }
    });

    // File input change
    fileInput.on('change', function() {
        handleFile(this.files[0]);
    });

    // Drag and drop events
    uploadZone.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });

    uploadZone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });

    uploadZone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            handleFile(files[0]);
        }
    });

    // Remove file button
    removeFileBtn.on('click', function() {
        fileInput.val('');
        filePreview.hide();
        uploadZoneContent.show();
        submitBtn.prop('disabled', false);
    });

    // Handle file display
    function handleFile(file) {
        if (!file) return;

        // Validate file type
        const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please upload only PDF or DOCX files.');
            fileInput.val('');
            return;
        }

        // Validate file size (10MB)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 10MB.');
            fileInput.val('');
            return;
        }

        // Display file info
        const fileName = file.name;
        const fileSize = formatFileSize(file.size);
        const fileType = file.type.includes('pdf') ? 'PDF' : 'DOCX';
        const fileIcon = file.type.includes('pdf') ? 'fa-file-pdf' : 'fa-file-word';

        $('#fileName').text(fileName);
        $('#fileSize').text(fileSize);
        $('#fileType').text(fileType);
        $('#fileIcon i').removeClass().addClass('fas ' + fileIcon);

        // Show preview, hide upload zone content
        uploadZoneContent.hide();
        filePreview.show();
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Form submission
    uploadForm.on('submit', function(e) {
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    });
});
</script>
@endpush
