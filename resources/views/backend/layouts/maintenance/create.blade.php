@extends('backend.app')

@section('title', 'Create Maintenance Request')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Create Maintenance Request</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('maintanance.index') }}">Maintenance</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Request Information</h3>
                            </div>
                            <div class="card-body">
                                <form id="maintenanceForm" action="{{ route('maintanance.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    {{-- Row 1: Title + Tenant --}}
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Ticket Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" id="title"
                                                placeholder="Enter Ticket Title" required>
                                        </div>

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
                                                    <option value="{{ $tenant->id }}">{{ $fullName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Row 2: Property (loaded via AJAX) + Unit --}}
                                    <div class="row mb-3" id="propertyRow" style="display: none !important;">
                                        <div class="col-md-6">
                                            <label class="form-label">Property</label>
                                            <select class="form-select" name="property_id" id="propertySelect">
                                                <option value="">-- Select Tenant First --</option>
                                            </select>
                                            <div class="invalid-feedback" id="propertyError"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Unit <span
                                                    class="text-muted">(optional)</span></label>
                                            <input type="text" class="form-control" name="unit" id="unitInput"
                                                placeholder="e.g. Apt 201, Room 3B">
                                        </div>
                                    </div>

                                    {{-- Lease Info Alert Box --}}
                                    <div class="mb-4" id="leaseInfoBox" style="display: none;"></div>

                                    {{-- No Lease Warning --}}
                                    <div class="mb-4" id="noLeaseBox" style="display: none;">
                                        <div class="alert alert-warning d-flex align-items-center mb-0">
                                            <i class="fe fe-alert-triangle me-2"></i>
                                            <span>This tenant has no active lease. The request will be saved without a
                                                property.</span>
                                        </div>
                                    </div>

                                    {{-- Category --}}
                                    <div class="mb-4">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <div class="row g-3">
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category" id="category_ac"
                                                    value="ac" required>
                                                <label class="category-card" for="category_ac">
                                                    <div class="category-icon"><i class="fe fe-wind"></i></div>
                                                    <div class="category-label">A/C</div>
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_appliance" value="appliance">
                                                <label class="category-card" for="category_appliance">
                                                    <div class="category-icon"><i class="fe fe-box"></i></div>
                                                    <div class="category-label">Appliance</div>
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_electrical" value="electrical">
                                                <label class="category-card" for="category_electrical">
                                                    <div class="category-icon"><i class="fe fe-zap"></i></div>
                                                    <div class="category-label">Electrical</div>
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_heat" value="heat">
                                                <label class="category-card" for="category_heat">
                                                    <div class="category-icon"><i class="fe fe-thermometer"></i></div>
                                                    <div class="category-label">Heat</div>
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_kitchen" value="kitchen">
                                                <label class="category-card" for="category_kitchen">
                                                    <div class="category-icon"><i class="fa-solid fa-mug-hot"
                                                            style="font-size:25px"></i></div>
                                                    <div class="category-label">Kitchen</div>
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_plumbing" value="plumbing">
                                                <label class="category-card" for="category_plumbing">
                                                    <div class="category-icon"><i class="fe fe-droplet"></i></div>
                                                    <div class="category-label">Plumbing</div>
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-sm-6">
                                                <input type="radio" class="btn-check" name="category"
                                                    id="category_other" value="other">
                                                <label class="category-card" for="category_other">
                                                    <div class="category-icon"><i class="fe fe-more-horizontal"></i></div>
                                                    <div class="category-label">Other</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="mb-4">
                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" id="description" rows="5" placeholder="Enter Description"
                                            required></textarea>
                                    </div>

                                    {{-- Checkboxes --}}
                                    <div class="mb-4">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_urgent"
                                                id="isUrgent" value="1">
                                            <label class="form-check-label" for="isUrgent">Mark as Urgent</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="grant_permission"
                                                id="grantPermission" value="1">
                                            <label class="form-check-label" for="grantPermission">
                                                Consent to enter the premises: Granted
                                            </label>
                                        </div>
                                    </div>

                                    {{-- File Upload --}}
                                    <div class="mb-4">
                                        <label class="form-label">Upload Photos, Videos, and Documents
                                            <span class="text-muted">(0/20)</span>
                                        </label>
                                        <div class="upload-area" id="uploadArea">
                                            <div class="upload-placeholder">
                                                <i class="fe fe-image upload-icon"></i>
                                                <h6 class="mt-3">Drag & Drop</h6>
                                                <p class="text-muted mb-1">or <span
                                                        class="text-primary browse-text">browse</span> photos</p>
                                                <small class="text-muted">Supported: .jpg .jpeg .png .pdf .bmp .jfif .mp4
                                                    .mov .webm .mpeg .m4v (max 20MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" name="attachments[]" id="fileInput" multiple
                                            accept=".jpg,.jpeg,.png,.pdf,.bmp,.jfif,.mp4,.mov,.webm,.mpeg,.m4v"
                                            style="display: none;">
                                        <div id="filePreview" class="mt-3 row g-3"></div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('maintanance.index') }}" class="btn btn-light">Cancel</a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fe fe-save me-1"></i> Create Request
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

            /*===========================================
             | TENANT → LEASE PROPERTIES AJAX LOADER
             ===========================================*/
            $('#tenantSelect').on('change', function() {
                const tenantId = $(this).val();

                // Reset UI
                $('#propertySelect').html('<option value="">Loading...</option>').prop('disabled', true);
                $('#leaseInfoBox').hide().html('');
                $('#noLeaseBox').hide();
                $('#propertyRow').hide();

                if (!tenantId) return;

                $.ajax({
                    url: '{{ route('maintanance.tenant.lease.properties') }}',
                    type: 'GET',
                    data: {
                        tenant_id: tenantId
                    },
                    success: function(res) {
                        $('#propertyRow').show();
                        $('#propertySelect').html(
                            '<option value="">-- Select Property --</option>');

                        if (res.success && res.leases.length > 0) {
                            res.leases.forEach(function(lease) {
                                $('#propertySelect').append(
                                    `<option value="${lease.property_id}" data-lease='${JSON.stringify(lease)}'>
                    ${lease.property_name}${lease.address ? ' — ' + lease.address : ''} (${lease.status})
                </option>`
                                );
                            });

                            $('#propertySelect').prop('disabled', false);

                            const activeLease = res.leases.find(l => l.status === 'ACTIVE') ||
                                res.leases[0];
                            if (activeLease) {
                                $('#propertySelect').val(activeLease.property_id).trigger(
                                    'change');
                            }

                        } else {
                            $('#propertySelect').html(
                                '<option value="">No lease found</option>').prop('disabled',
                                true);
                            $('#noLeaseBox').show();
                        }
                    },
                    error: function() {
                        $('#propertyRow').show();
                        $('#propertySelect').html(
                            '<option value="">Error loading properties</option>').prop(
                            'disabled', false);
                        toastr.error('Failed to load tenant lease properties');
                    }
                });
            });

            // Show lease info card when property is selected
            $('#propertySelect').on('change', function() {
                const leaseData = $(this).find(':selected').data('lease');
                $('#leaseInfoBox').hide().html('');

                if (!leaseData) return;

                const statusColor = getStatusColor(leaseData.status);
                const startDate = formatDate(leaseData.start_date);
                const endDate = formatDate(leaseData.end_date);

                $('#leaseInfoBox').html(`
            <div class="card border-0 bg-light mb-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fe fe-home text-primary me-2"></i>
                        <strong class="me-2">Lease Details</strong>
                        <span class="badge bg-${statusColor}">${leaseData.status}</span>
                    </div>
                    <div class="row g-2 text-sm">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Property</small>
                            <span class="fw-semibold">${leaseData.property_name}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Address</small>
                            <span>${leaseData.address || 'N/A'}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Start Date</small>
                            <span>${startDate}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">End Date</small>
                            <span>${endDate}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Rent</small>
                            <span class="fw-semibold text-success">$${parseFloat(leaseData.rent_amount).toFixed(2)}/mo</span>
                        </div>
                    </div>
                </div>
            </div>
        `).show();
            });

            // Helper: badge color based on lease status
            function getStatusColor(status) {
                const map = {
                    'ACTIVE': 'success',
                    'PENDING_TENANT_SIGN': 'warning',
                    'PENDING_ADMIN_SIGN': 'info',
                    'DRAFT': 'secondary',
                    'TERMINATED': 'danger',
                    'COMPLETED': 'primary',
                };
                return map[status] || 'secondary';
            }

            // Helper: format date string
            function formatDate(dateStr) {
                if (!dateStr) return 'N/A';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }

            /*===========================================
             | FILE UPLOAD HANDLER
             ===========================================*/
            let selectedFiles = [];
            const maxFiles = 20;

            function updateFileCounter() {
                $('.upload-area').prev('label').find('span').text(`(${selectedFiles.length}/${maxFiles})`);
            }

            $('#uploadArea').on('click', function(e) {
                e.preventDefault();
                $('#fileInput').click();
            });

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

            $('#uploadArea').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
                handleFiles(e.originalEvent.dataTransfer.files);
            });

            $('#fileInput').on('change', function() {
                handleFiles(this.files);
                $(this).val('');
            });

            function handleFiles(files) {
                Array.from(files).forEach(file => {
                    if (selectedFiles.length >= maxFiles) {
                        toastr.error(`Maximum ${maxFiles} files allowed`);
                        return;
                    }
                    if (file.size > 20 * 1024 * 1024) {
                        toastr.error(`"${file.name}" exceeds 20MB limit`);
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
                    let html = '';

                    if (fileType === 'image') {
                        html = `<div class="col-md-3 file-preview-col" data-file-index="${fileIndex}">
                            <div class="file-preview-item">
                                <img src="${e.target.result}" alt="${file.name}">
                                <button type="button" class="remove-file" onclick="removeFile(${fileIndex})">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </div>`;
                    } else if (fileType === 'video') {
                        html = `<div class="col-md-3 file-preview-col" data-file-index="${fileIndex}">
                            <div class="file-preview-item">
                                <video style="width:100%;height:100%;object-fit:cover;">
                                    <source src="${e.target.result}">
                                </video>
                                <button type="button" class="remove-file" onclick="removeFile(${fileIndex})">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </div>`;
                    } else {
                        html = `<div class="col-md-3 file-preview-col" data-file-index="${fileIndex}">
                            <div class="file-preview-item">
                                <div class="file-icon">
                                    <i class="fe fe-file"></i>
                                    <small class="mt-2">${extension}</small>
                                </div>
                                <button type="button" class="remove-file" onclick="removeFile(${fileIndex})">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </div>`;
                    }
                    $('#filePreview').append(html);
                };

                reader.readAsDataURL(file);
            }

            window.removeFile = function(index) {
                selectedFiles.splice(index, 1);
                $(`.file-preview-col[data-file-index="${index}"]`).remove();
                $('.file-preview-col').each(function(i) {
                    $(this).attr('data-file-index', i);
                    $(this).find('.remove-file').attr('onclick', `removeFile(${i})`);
                });
                updateFileCounter();
            };

            /*===========================================
             | FORM SUBMIT
             ===========================================*/
            $('#maintenanceForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.delete('attachments[]');
                selectedFiles.forEach(file => formData.append('attachments[]', file));

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
                        if (xhr.responseJSON?.errors) {
                            $.each(xhr.responseJSON.errors, (key, value) => toastr.error(value[
                                0]));
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
        /* ---- Category Cards ---- */
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

        /* ---- Upload Area ---- */
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

        /* ---- File Preview ---- */
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
            background: rgba(220, 53, 69, .9);
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

        /* ---- Lease Info Card ---- */
        #leaseInfoBox .card {
            border-left: 4px solid #0d6efd !important;
        }

        .text-sm {
            font-size: 0.875rem;
        }
    </style>
@endpush
