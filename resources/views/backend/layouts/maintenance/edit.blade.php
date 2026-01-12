@extends('backend.app')

@section('title', 'Edit Maintenance Request')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Edit Maintenance Request</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('maintanance.index') }}">Maintenance</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Edit Request Information</h3>
                            </div>
                            <div class="card-body">
                                <form id="maintenanceForm" action="{{ route('maintanance.update', $maintenance->id) }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf




                                    <div class="row mb-4">
                                        <!-- Title -->
                                        <div class="col-md-6">

                                            <label class="form-label">Ticket Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" id="title"
                                                placeholder="Enter Ticket Title" value="{{ $maintenance->title }}" required>
                                        </div>

                                        {{-- Select  tenant--}}
                                        <div class="col-md-6">
                                            <label class="form-label">Requested By <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="tenant_id" id="tenantSelect" required>
                                                <option value="">Select Tenant</option>
                                                @foreach ($tenants as $tenant)
                                                    @php
                                                        $profile = $tenant->profile;
                                                        $fullName = $profile
                                                            ? trim(
                                                                $profile->first_name .
                                                                    ' ' .
                                                                    ($profile->middle_name ?? '') .
                                                                    ' ' .
                                                                    ($profile->last_name ?? ''),
                                                            )
                                                            : $tenant->email;
                                                    @endphp
                                                    <option value="{{ $tenant->id }}"
                                                        {{ $maintenance->tenant_id == $tenant->id ? 'selected' : '' }}>
                                                        {{ $fullName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Property <span class="text-danger">*</span></label>
                                            <select class="form-select" name="property_id" id="propertySelect" required>
                                                <option value="">Select Property</option>
                                                @foreach ($properties as $property)
                                                    <option value="{{ $property->id }}"
                                                        {{ $maintenance->property_id == $property->id ? 'selected' : '' }}>
                                                        {{ $property->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Units</label>
                                            <select class="form-select" name="unit" id="unitSelect">
                                                <option value="">Select Units</option>
                                                @if ($maintenance->unit)
                                                    <option value="{{ $maintenance->unit }}" selected>
                                                        {{ $maintenance->unit }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div> --}}

                                    <!-- Category Selection -->
                                    <div class="mb-4">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <div class="row g-3">
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category" id="category_ac"
                                                    value="ac" {{ $maintenance->category == 'ac' ? 'checked' : '' }}
                                                    required>
                                                <label class="category-card" for="category_ac">
                                                    <div class="category-icon">
                                                        <i class="fe fe-wind"></i>
                                                    </div>
                                                    <div class="category-label">A/C</div>
                                                </label>
                                            </div>

                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_appliance" value="appliance"
                                                    {{ $maintenance->category == 'appliance' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_appliance">
                                                    <div class="category-icon">
                                                        <i class="fe fe-box"></i>
                                                    </div>
                                                    <div class="category-label">Appliance</div>
                                                </label>
                                            </div>

                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_electrical" value="electrical"
                                                    {{ $maintenance->category == 'electrical' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_electrical">
                                                    <div class="category-icon">
                                                        <i class="fe fe-zap"></i>
                                                    </div>
                                                    <div class="category-label">Electrical</div>
                                                </label>
                                            </div>

                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category" id="category_heat"
                                                    value="heat" {{ $maintenance->category == 'heat' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_heat">
                                                    <div class="category-icon">
                                                        <i class="fe fe-thermometer"></i>
                                                    </div>
                                                    <div class="category-label">Heat</div>
                                                </label>
                                            </div>

                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_kitchen" value="kitchen"
                                                    {{ $maintenance->category == 'kitchen' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_kitchen">
                                                    <div class="category-icon">
                                                        <i class="fa-solid fa-mug-hot" style="font-size: 25px"></i>
                                                    </div>
                                                    <div class="category-label">Kitchen</div>
                                                </label>
                                            </div>

                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_plumbing" value="plumbing"
                                                    {{ $maintenance->category == 'plumbing' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_plumbing">
                                                    <div class="category-icon">
                                                        <i class="fe fe-droplet"></i>
                                                    </div>
                                                    <div class="category-label">Plumbing</div>
                                                </label>
                                            </div>

                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_other" value="other"
                                                    {{ $maintenance->category == 'other' ? 'checked' : '' }}>
                                                <label class="category-card" for="category_other">
                                                    <div class="category-icon">
                                                        <i class="fe fe-more-horizontal"></i>
                                                    </div>
                                                    <div class="category-label">Other</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-4">
                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" id="description" rows="5" placeholder="Enter Description"
                                            required>{{ $maintenance->description }}</textarea>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status" required>
                                            <option value="pending"
                                                {{ $maintenance->status == 'pending' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress"
                                                {{ $maintenance->status == 'in_progress' ? 'selected' : '' }}>In Progress
                                            </option>
                                            <option value="completed"
                                                {{ $maintenance->status == 'completed' ? 'selected' : '' }}>Resolved
                                            </option>
                                            <option value="rejected"
                                                {{ $maintenance->status == 'rejected' ? 'selected' : '' }}>Rejected
                                            </option>
                                            <option value="cancelled"
                                                {{ $maintenance->status == 'cancelled' ? 'selected' : '' }}>Cancelled
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Checkboxes -->
                                    <div class="mb-4">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_urgent"
                                                id="isUrgent" value="1"
                                                {{ $maintenance->is_urgent ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isUrgent">
                                                Mark as Urgent
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="grant_permission"
                                                id="grantPermission" value="1"
                                                {{ $maintenance->grant_permission ? 'checked' : '' }}>
                                            <label class="form-check-label" for="grantPermission">
                                                Consent to enter the premises: Granted
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Existing Attachments -->
                                    @if ($maintenance->attachments->count() > 0)
                                        <div class="mb-4">
                                            <label class="form-label">Existing Attachments</label>
                                            <div class="row g-3">
                                                @foreach ($maintenance->attachments as $attachment)
                                                    <div class="col-md-3">
                                                        <div class="file-preview-item">
                                                            @php
                                                                $extension = pathinfo(
                                                                    $attachment->attachment_path,
                                                                    PATHINFO_EXTENSION,
                                                                );
                                                                $isImage = in_array(strtolower($extension), [
                                                                    'jpg',
                                                                    'jpeg',
                                                                    'png',
                                                                    'gif',
                                                                    'bmp',
                                                                    'jfif',
                                                                ]);
                                                                $isVideo = in_array(strtolower($extension), [
                                                                    'mp4',
                                                                    'mov',
                                                                    'webm',
                                                                    'mpeg',
                                                                    'm4v',
                                                                ]);
                                                            @endphp

                                                            @if ($isImage)
                                                                <img src="{{ asset('storage/' . $attachment->attachment_path) }}"
                                                                    alt="Attachment">
                                                            @elseif($isVideo)
                                                                <video
                                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                                                    <source
                                                                        src="{{ asset('storage/' . $attachment->attachment_path) }}">
                                                                </video>
                                                            @else
                                                                <div class="file-icon">
                                                                    <i class="fe fe-file"></i>
                                                                    <small
                                                                        class="mt-2">{{ strtoupper($extension) }}</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- File Upload Section -->
                                    <div class="mb-4">
                                        <label class="form-label">Add More Photos, Videos, and Documents <span
                                                class="text-muted">(0/20)</span></label>
                                        <div class="upload-area" id="uploadArea">
                                            <div class="upload-placeholder">
                                                <i class="fe fe-image upload-icon"></i>
                                                <h6 class="mt-3">Drag & Drop</h6>
                                                <p class="text-muted mb-1">or <span
                                                        class="text-primary browse-text">browse</span> photos</p>
                                                <small class="text-muted">File Format supported .jpg .jpeg .png .pdf .bmp
                                                    .jfif .mp4 .mov .webm .mpeg .m4v (max file size 20MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" name="attachments[]" id="fileInput" multiple
                                            accept=".jpg,.jpeg,.png,.pdf,.bmp,.jfif,.mp4,.mov,.webm,.mpeg,.m4v"
                                            style="display: none;">

                                        <div id="filePreview" class="mt-3 row g-3"></div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('maintanance.index') }}" class="btn btn-light">Cancel</a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fe fe-save me-1"></i> Update Request
                                        </button>
                                    </div>
                                </form>
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
            let selectedFiles = [];
            const maxFiles = 20;

            // Update file counter
            function updateFileCounter() {
                $('.upload-area').prev('label').find('span').text(`(${selectedFiles.length}/${maxFiles})`);
            }

            // Upload area click handler
            $('#uploadArea').on('click', function(e) {
                e.preventDefault();
                $('#fileInput').click();
            });

            // Prevent default drag behaviors
            $('#uploadArea').on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });

            $('#uploadArea').on('dragleave dragend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });

            // Handle dropped files
            $('#uploadArea').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');

                const files = e.originalEvent.dataTransfer.files;
                handleFiles(files);
            });

            // File input change
            $('#fileInput').on('change', function() {
                handleFiles(this.files);
                // Reset input so same file can be selected again
                $(this).val('');
            });

            function handleFiles(files) {
                if (selectedFiles.length >= maxFiles) {
                    toastr.error(`Maximum ${maxFiles} files allowed`);
                    return;
                }

                Array.from(files).forEach(file => {
                    if (selectedFiles.length >= maxFiles) {
                        toastr.error(`Maximum ${maxFiles} files allowed`);
                        return;
                    }

                    if (file.size > 20 * 1024 * 1024) {
                        toastr.error(`File "${file.name}" exceeds 20MB limit`);
                        return;
                    }

                    selectedFiles.push(file);
                    displayFilePreview(file);
                });

                updateFileCounter();
            }

            function displayFilePreview(file) {
                const reader = new FileReader();
                const fileIndex = selectedFiles.length - 1;

                reader.onload = function(e) {
                    const fileType = file.type.split('/')[0];
                    const extension = file.name.split('.').pop().toUpperCase();
                    let previewHTML = '';

                    if (fileType === 'image') {
                        previewHTML = `
                            <div class="col-md-3 file-preview-col" data-file-index="${fileIndex}">
                                <div class="file-preview-item">
                                    <img src="${e.target.result}" alt="${file.name}">
                                    <button type="button" class="remove-file" onclick="removeFile(${fileIndex})">
                                        <i class="fe fe-x"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    } else if (fileType === 'video') {
                        previewHTML = `
                            <div class="col-md-3 file-preview-col" data-file-index="${fileIndex}">
                                <div class="file-preview-item">
                                    <video style="width: 100%; height: 100%; object-fit: cover;">
                                        <source src="${e.target.result}">
                                    </video>
                                    <button type="button" class="remove-file" onclick="removeFile(${fileIndex})">
                                        <i class="fe fe-x"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    } else {
                        previewHTML = `
                            <div class="col-md-3 file-preview-col" data-file-index="${fileIndex}">
                                <div class="file-preview-item">
                                    <div class="file-icon">
                                        <i class="fe fe-file"></i>
                                        <small class="mt-2">${extension}</small>
                                    </div>
                                    <button type="button" class="remove-file" onclick="removeFile(${fileIndex})">
                                        <i class="fe fe-x"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    }

                    $('#filePreview').append(previewHTML);
                };

                reader.readAsDataURL(file);
            }

            // Remove file function (global scope)
            window.removeFile = function(index) {
                // Remove from array
                selectedFiles.splice(index, 1);

                // Remove preview
                $(`.file-preview-col[data-file-index="${index}"]`).remove();

                // Update remaining indices
                $('.file-preview-col').each(function(i) {
                    $(this).attr('data-file-index', i);
                    $(this).find('.remove-file').attr('onclick', `removeFile(${i})`);
                });

                updateFileCounter();
            };

            // Form submission
            $('#maintenanceForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                // Remove existing file inputs and append selected files
                formData.delete('attachments[]');
                selectedFiles.forEach(file => {
                    formData.append('attachments[]', file);
                });

                const submitBtn = $('#submitBtn');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin me-1"></i> Processing...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .category-card {
            display: block;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fff;
            height: 100%;
        }

        .category-card:hover {
            border-color: #D9A600;
            background: #f8f9fa;
        }

        .btn-check:checked+.category-card {
            border-color: #D9A600;
            background: #d9a6001f;
        }

        .category-icon {
            font-size: 36px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .btn-check:checked+.category-card .category-icon {
            color: #D9A600;
        }

        .category-label {
            font-weight: 500;
            color: #2c3e50;
        }

        .upload-area {
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafbfc;
        }

        .upload-area:hover,
        .upload-area.drag-over {
            border-color: #D9A600;
            background: #f0f7ff;
        }

        .upload-icon {
            font-size: 48px;
            color: #cbd5e0;
        }

        .browse-text {
            cursor: pointer;
            text-decoration: underline;
        }

        .upload-placeholder {
            pointer-events: none;
        }

        .file-preview-item {
            position: relative;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .file-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        .file-icon i {
            font-size: 48px;
            color: #6c757d;
        }

        .file-icon small {
            font-weight: 600;
            color: #6c757d;
        }

        .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            z-index: 10;
        }

        .remove-file:hover {
            background: #dc3545;
        }
    </style>
@endpush
