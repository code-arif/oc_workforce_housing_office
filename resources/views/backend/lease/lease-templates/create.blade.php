@extends('backend.app')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Upload New Lease Template</h2>
                        <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Templates
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <!-- Instructions Card -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                <li>Upload your lease agreement document (PDF or DOCX format)</li>
                                <li>The system will convert it to an editable format</li>
                                <li>After upload, you'll be able to add dynamic placeholders</li>
                                <li>Add signature fields for admin and tenant</li>
                                <li>Save the template for future use</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Upload Form Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Template Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('lease-templates.store') }}" 
                                method="POST" 
                                enctype="multipart/form-data" 
                                id="uploadForm">
                                @csrf

                                <!-- Template Name -->
                                <div class="form-group mb-4">
                                    <label for="name" class="form-label">
                                        Template Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                        name="name" 
                                        id="name" 
                                        class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                        placeholder="e.g., Residential Lease Agreement 2024"
                                        value="{{ old('name') }}"
                                        required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Give your template a descriptive name for easy identification
                                    </small>
                                </div>

                                <!-- File Upload -->
                                <div class="form-group mb-4">
                                    <label for="template_file" class="form-label">
                                        Upload Template File <span class="text-danger">*</span>
                                    </label>
                                    
                                    <div class="upload-area" id="uploadArea">
                                        <div class="upload-icon">
                                            <i class="fas fa-cloud-upload-alt fa-4x text-primary"></i>
                                        </div>
                                        <h5>Drag & Drop your file here</h5>
                                        <p class="text-muted">or click to browse</p>
                                        <input type="file" 
                                            name="template_file" 
                                            id="template_file" 
                                            class="form-control @error('template_file') is-invalid @enderror" 
                                            accept=".pdf,.docx"
                                            required
                                            style="display: none;">
                                        <button type="button" class="btn btn-primary" id="browseBtn">
                                            <i class="fas fa-folder-open"></i> Browse Files
                                        </button>
                                    </div>

                                    @error('template_file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <!-- File Info Display -->
                                    <div id="fileInfo" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-file-alt"></i>
                                                    <strong id="fileName"></strong>
                                                    <span class="badge bg-primary ms-2" id="fileType"></span>
                                                    <br>
                                                    <small class="text-muted" id="fileSize"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger" id="removeFile">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Accepted formats: PDF (.pdf), Word Document (.docx) | Maximum size: 10MB
                                    </small>
                                </div>

                                <!-- Supported Placeholders Info -->
                                <div class="form-group mb-4">
                                    <label class="form-label">Available Placeholders</label>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p class="mb-2"><strong>After uploading, you can add these dynamic fields:</strong></p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li><i class="fas fa-tag text-primary"></i> Tenant Name</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Tenant Email</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Tenant Phone</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Property Address</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Lease Start Date</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Lease End Date</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li><i class="fas fa-tag text-primary"></i> Monthly Rent</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Security Deposit</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Lease Term</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Admin Name</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Admin Email</li>
                                                        <li><i class="fas fa-tag text-primary"></i> Current Date</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-upload"></i> Upload Template
                                    </button>
                                    <a href="{{ route('lease-templates.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tips Card -->
                    <div class="card mt-4 border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Tips for Best Results</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li><strong>Use well-formatted documents:</strong> Properly formatted documents convert better</li>
                                <li><strong>DOCX recommended:</strong> Word documents preserve formatting better than PDFs</li>
                                <li><strong>Remove signatures:</strong> Upload blank templates without pre-filled signatures</li>
                                <li><strong>Clear layout:</strong> Use clear headings and proper spacing for better readability</li>
                                <li><strong>Standard fonts:</strong> Stick to common fonts like Arial, Times New Roman, or Calibri</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const uploadArea = $('#uploadArea');
    const fileInput = $('#template_file');
    const browseBtn = $('#browseBtn');
    const fileInfo = $('#fileInfo');
    const removeFileBtn = $('#removeFile');
    const uploadForm = $('#uploadForm');

    // Browse button click
    browseBtn.on('click', function() {
        fileInput.click();
    });

    // Upload area click
    // uploadArea.on('click', function(e) {
    //     if (e.target !== browseBtn[0]) {
    //         fileInput.click();
    //     }
    // });
    uploadArea.on('click', function(e) { 
        if ($(e.target).is('#browseBtn') || $(e.target).is('#template_file')) 
            return; fileInput.click(); 
    });

    // File input change
    fileInput.on('change', function() {
        handleFileSelect(this.files);
    });

    // Drag and drop functionality
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });

    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });

    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput.prop('files', files);
            handleFileSelect(files);
        }
    });

    // Handle file selection
    function handleFileSelect(files) {
        if (files.length === 0) return;

        const file = files[0];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB

        // Validate file type
        if (fileExtension !== 'pdf' && fileExtension !== 'docx') {
            alert('Please upload only PDF or DOCX files');
            fileInput.val('');
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            fileInput.val('');
            return;
        }

        // Display file info
        $('#fileName').text(file.name);
        $('#fileType').text(fileExtension.toUpperCase());
        $('#fileSize').text(`Size: ${fileSize} MB`);
        
        uploadArea.hide();
        fileInfo.show();
    }

    // Remove file
    removeFileBtn.on('click', function() {
        fileInput.val('');
        fileInfo.hide();
        uploadArea.show();
    });

    // Form submission
    uploadForm.on('submit', function() {
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        // Show progress indicator
        if (fileInput[0].files.length > 0) {
            showProgressBar();
        }
    });

    function showProgressBar() {
        const progressHtml = `
            <div class="alert alert-info mt-3" id="uploadProgress">
                <strong>Uploading and converting...</strong>
                <div class="progress mt-2">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 100%"></div>
                </div>
                <small class="text-muted">This may take a few moments depending on file size</small>
            </div>
        `;
        fileInfo.after(progressHtml);
    }
});
</script>
@endpush

@push('styles')
<style>
    .upload-area {
        border: 3px dashed #dee2e6;
        border-radius: 8px;
        padding: 60px 20px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: #0d6efd;
        background: #e7f1ff;
    }

    .upload-area.drag-over {
        border-color: #0d6efd;
        background: #e7f1ff;
        transform: scale(1.02);
    }

    .upload-icon {
        margin-bottom: 20px;
    }

    .upload-area h5 {
        color: #495057;
        margin-bottom: 10px;
    }

    .upload-area p {
        margin-bottom: 20px;
    }

    #fileInfo .alert {
        margin-bottom: 0;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
        font-weight: 600;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }

    .list-unstyled li {
        padding: 5px 0;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .border-info {
        border-color: #0dcaf0 !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
    }

    #uploadProgress {
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .progress {
        height: 25px;
    }

    .progress-bar {
        font-size: 0.875rem;
        line-height: 25px;
    }
</style>
@endpush